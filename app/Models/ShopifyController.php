<?php
namespace App\Controllers;


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');


use Core\Controller;
use PDO;
use NetSuite\NetSuiteService;
use NetSuite\Classes\AddRequest;
use NetSuite\Classes\ItemSearchBasic;
use NetSuite\Classes\SearchStringField;
use NetSuite\Classes\PriceLevelSearch;
use NetSuite\Classes\PriceLevelSearchBasic;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\InventoryItem;
use NetSuite\Classes\UpdateRequest;
use NetSuite\Classes\RecordRef;
use NetSuite\Classes\GetRequest;
use NetSuite\Classes\PricingMatrix;
use NetSuite\Classes\Pricing;
use NetSuite\Classes\Customer;
use NetSuite\Classes\CustomerSearchBasic;
use NetSuite\Classes\PriceList;
use NetSuite\Classes\Price;
use NetSuite\Classes\AssemblyItem;
use NetSuite\Classes\SalesOrder;
use NetSuite\Classes\SalesOrderItem;
use NetSuite\Classes\SalesOrderItemList;
use NetSuite\Classes\CustomFieldList;
use NetSuite\Classes\StringCustomFieldRef;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

class ShopifyController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->initNetSuiteService();
    }

    private function initNetSuiteService() {
        $netsuiteModel = $this->loadModel('NetSuiteModel');
        $settings = $netsuiteModel->getSettingsByUserId(1); // Example user ID
        $config = [
            "endpoint"            => "2021_1",
            "host"                => "https://webservices.netsuite.com",
            "account"             => "6825553",
            "consumerKey"         => $settings['consumer_key'],
            "consumerSecret"      => $settings['consumer_secret'],
            "token"               => $settings['token_id'],
            "tokenSecret"         => $settings['token_secret'],
            "signatureAlgorithm"  => 'sha256',
            "logging"             => true,
            "log_path"            => "/var/www/myapp/logs/netsuite",
            "log_format"          => "netsuite-php-%date-%operation",
            "log_dateformat"      => "Ymd.His.u",
        ];
        $this->netsuiteService = new NetSuiteService($config);
    }

    public function updateNetSuiteItem($updatedData = array(), $prod_Title = '', $prod_body = '', $prod_vendor = '', $topic, $logMessages) {
        $service = $this->netsuiteService;
        $webhookModel = $this->loadModel('ShopifyWebhookModel');

        try {
            if($updatedData->sku != null && $updatedData->sku != '') {
                $logMessages[] = "Start Updating Item SKU : ".$updatedData->sku;
                $search = new ItemSearchBasic();
                $search->itemId = new SearchStringField();


                $search->itemId->searchValue = $updatedData->sku;
                $search->itemId->operator = 'is';


                $searchRequest = new SearchRequest();
                $searchRequest->searchRecord = $search;
                $searchResponse = $service->search($searchRequest);

                if (!$searchResponse->searchResult->status->isSuccess || !($searchResponse->searchResult->recordList->record)) {
                    $logMessages[] = "Item not found, creating item.";
                    $this->createNetSuiteItem($updatedData, $prod_Title, $prod_body, $prod_vendor, $topic, $logMessages);
                    return;
                }


                $tempitem = $searchResponse->searchResult->recordList->record[0];



                $request = new GetRequest();
                $request->baseRef = new RecordRef();
                $request->baseRef->internalId = $tempitem->internalId;
                if (isset($tempitem->type) && $tempitem->type == 'inventoryItem') {
                    $logMessages[] = "Its inventoryItem";
                    $request->baseRef->type = "inventoryItem";
                } else {
                    $logMessages[] = "Its assemblyItem";
                    $request->baseRef->type = "assemblyItem";
                }

                $getResponse = $service->get($request);
                if ( ! $getResponse->readResponse->status->isSuccess) {
                    $logMessages[] = "GET ERROR: ".json_encode($getResponse->readResponse);
                } else {
                    $item = $getResponse->readResponse->record;
                }

                // Assuming the item to update is the first returned result
                $item->internalId = $item->internalId;

                if(isset($updatedData->title)) {
                    if($updatedData->title != 'Default Title' && $updatedData->title != '') {
                        $prod_Title = $updatedData->title;
                    }
                }

                $item->displayName = $prod_Title;

                if (isset($prod_body)) {
                    $item->description = strip_tags($prod_body);
                }

                // $subsidiary = new RecordRef();
                // $subsidiary->internalId = '2';
                // $item->subsidiaryList = new \stdClass();
                // $item->subsidiaryList->recordRef = [$subsidiary];


                if (isset($updatedData->price)) {
                    if (!isset($item->pricingMatrix)) {
                        $item->pricingMatrix = new PricingMatrix();
                        $item->pricingMatrix->pricing = [];
                    }

                    foreach ($item->pricingMatrix->pricing as $pricing) {
                        // Calculate the new price based on the discount
                        if (isset($pricing->discount) && $pricing->discount != 0) {
                            // Convert discount to a positive value if it's negative
                            $discountPercentage = abs($pricing->discount); // Absolute value to ensure positive percentage
                            $newPriceValue = $updatedData->price * (1 - ($discountPercentage / 100));
                        } else {
                            $newPriceValue = $updatedData->price;  // No discount, use the base price
                        }

                        // Update the price object
                        if (isset($pricing->priceList) && isset($pricing->priceList->price[0])) {
                            $pricing->priceList->price[0]->value = $newPriceValue;
                        } else {
                            // If there is no price set in the priceList, initialize it
                            $priceList = new PriceList();
                            $price = new Price(['value' => $newPriceValue]);
                            $priceList->price = [$price];
                            $pricing->priceList = $priceList;
                        }
                    }
                }

                if (isset($updatedData->taxable)) {
                    $item->isTaxable = $updatedData->taxable;
                }
                if (isset($updatedData->weight)) {
                    $item->weight = $updatedData->weight;
                }
                if (isset($prod_vendor)) {
                    $item->manufacturer = $prod_vendor;
                }
                if(isset($updatedData->inventory_quantity)) {
                    // $item->minimumQuantity = $updatedData->inventory_quantity;
                }

                if (isset($updatedData->taxable)) {
                    $item->isTaxable = true;
                    $taxSchedule = new RecordRef();
                    $taxSchedule->internalId = '1';
                    $item->taxSchedule = $taxSchedule;
                } else {
                    $item->isTaxable = false;
                    $taxSchedule = new RecordRef();
                    $taxSchedule->internalId = '2';
                    $item->taxSchedule = $taxSchedule;
                }


                $updateRequest = new UpdateRequest();
                $updateRequest->record = $item;    
                $updateResponse = $service->update($updateRequest);
                if ($updateResponse->writeResponse->status->isSuccess) {
                    $logMessages[] = "Item updated successfully.";
                } else {
                    $logMessages[] = "Failed to update item: " . $updateResponse->writeResponse->status->statusDetail[0]->message;
                }
                $webhookId = $webhookModel->saveWebhook($topic, $updatedData, implode("\n", $logMessages));
            }
        } catch (Exception $e) {
            $logMessages[] = "Error creating item: " . $e->getMessage();
            $webhookId = $webhookModel->saveWebhook($topic, $updatedData, implode("\n", $logMessages));
        }
    }

    private function calculatePriceForLevel($basePrice, $discounts) {
        if ($discounts != '' || $discounts != 0) {
            $discountPercentage = abs($discounts); // Ensure the discount is positive
            return $basePrice * (1 - ($discountPercentage / 100));
        }
        return $basePrice; // Return base price if no discount is found for the level
    }


    public function createNetSuiteItem($updatedData = array(), $prod_Title = '', $prod_body = '', $prod_vendor = '', $topic, $logMessages) {
        $webhookModel = $this->loadModel('ShopifyWebhookModel');

        try {
            $service = $this->netsuiteService;
            if($updatedData->sku != null && $updatedData->sku != '') {
                $logMessages[] = "Create Product Start";

                $request = new GetRequest();
                $request->baseRef = new RecordRef();
                $request->baseRef->internalId = 12001;
                $request->baseRef->type = "inventoryItem";

                $getResponse = $service->get($request);
                if ( ! $getResponse->readResponse->status->isSuccess) {
                    $logMessages[] = "Get Error getting 12001";
                } else {
                    $olditem = $getResponse->readResponse->record;
                }




                $item = new AssemblyItem();
                $item->itemId = $updatedData->sku; // Assuming 'id' is unique and used as itemId
                $item->displayName = $prod_Title;
                $item->description = strip_tags($prod_body);

                $subsidiary = new RecordRef();
                $subsidiary->internalId = '2';
                $item->subsidiaryList = new \stdClass();
                $item->subsidiaryList->recordRef = [$subsidiary];


                $item->pricingMatrix = $olditem->pricingMatrix;

                $pricingEntries = [];
                foreach ($item->pricingMatrix->pricing as $level) {
                    $priceValue = $this->calculatePriceForLevel($updatedData->price, $level->discount);
                    $level->priceList->price[0]->value = $priceValue;
                }

                $item->weight = $updatedData->weight ?? null;
                $item->manufacturer = $prod_vendor;


                if (isset($updatedData->taxable)) {
                    $item->isTaxable = true;
                    $taxSchedule = new RecordRef();
                    $taxSchedule->internalId = '1';
                    $item->taxSchedule = $taxSchedule;
                } else {
                    $item->isTaxable = false;
                    $taxSchedule = new RecordRef();
                    $taxSchedule->internalId = '2';
                    $item->taxSchedule = $taxSchedule;
                }


                $request = new AddRequest();
                $request->record = $item;
                $response = $service->add($request);

                if ($response->writeResponse->status->isSuccess) {
                    $logMessages[] = "Item created successfully with ID: " . $response->writeResponse->baseRef->internalId;
                } else {
                    $logMessages[] = "Failed to create item: " . $response->writeResponse->status->statusDetail[0]->message;
                }
                $webhookId = $webhookModel->saveWebhook($topic, $updatedData, implode("\n", $logMessages));
            }
        } catch (Exception $e) {
            $logMessages[] = "Error creating item: " . $e->getMessage();
            $webhookId = $webhookModel->saveWebhook($topic, $updatedData, implode("\n", $logMessages));
        }

    }

    public function getShopPaymentSettings() {
        $shopifyModel = $this->loadModel('ShopifyModel');
        $settings = $shopifyModel->getSettingsByUserId(1); // Example user ID
        $shopDomain = 'domain.com';
        $accessToken = 'access.token';

        $url = "https://{$shopDomain}/admin/api/2023-01/shop.json";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "X-Shopify-Access-Token: {$accessToken}"
        ]);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        $data = json_decode($response, true);
        // The 'payment_settings' object holds your payment configuration
        return $data['shop']['payment_settings'] ?? [];
    }

    public function getCarrierServices() {
        $shopDomain = 'domain.com';
        $accessToken = 'access.token';
        $url = "https://{$shopDomain}/admin/api/2023-01/carrier_services.json";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "X-Shopify-Access-Token: {$accessToken}"
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);

        return json_decode($response, true);
    }


    public function mappingAction() {
        $ShopifypaymentMethods = [
            ['id' => '_omit', 'name' => 'Not Mapped'],
            ['id' => 'UNMAPPED', 'name' => 'Do Not Post'],
            ['id' => '6', 'name' => 'American Express'],
            ['id' => '1', 'name' => 'Cash'],
            ['id' => '2', 'name' => 'Check'],
            ['id' => '12', 'name' => 'Discover'],
            ['id' => '4', 'name' => 'Master Card'],
            ['id' => '17', 'name' => 'PayPal'],
            ['id' => '18', 'name' => 'Shop Pay Installment'],
            ['id' => '5', 'name' => 'VISA']
        ];

        $NetsuitepaymentMethods = [
            ['id' => '_omit', 'name' => 'Not Mapped'],
            ['id' => 'UNMAPPED', 'name' => 'Do Not Post'],
            ['id' => '6', 'name' => 'American Express'],
            ['id' => '1', 'name' => 'Cash'],
            ['id' => '2', 'name' => 'Check'],
            ['id' => '12', 'name' => 'Discover'],
            ['id' => '4', 'name' => 'Master Card'],
            ['id' => '17', 'name' => 'PayPal'],
            ['id' => '18', 'name' => 'Shop Pay Installment'],
            ['id' => '5', 'name' => 'VISA']
        ];

        $ShopifyshipmentMethods = [
            ['id' => '281', 'name' => 'Do Not Post'],
            ['id' => '282', 'name' => 'Not Mapped'],
            ['id' => '283', 'name' => 'UPS Ground (IID: 4)'],
            ['id' => '284', 'name' => 'GlobalTranz Standard (IID: 10249)'],
        ];

        $NetsuiteshipmentMethods = [
            ['id' => '281', 'name' => 'Do Not Post'],
            ['id' => '282', 'name' => 'Not Mapped'],
            ['id' => '283', 'name' => 'UPS Ground (IID: 4)'],
            ['id' => '284', 'name' => 'GlobalTranz Standard (IID: 10249)'],
        ];
        $shopifyModel = $this->loadModel('ShopifyModel');
        $settings = $shopifyModel->getSettingsByUserId(1); // Example user ID
        $this->render('settings/mapping.php', ['settings' => $settings, 'ShopifypaymentMethods' => $ShopifypaymentMethods, 'NetsuitepaymentMethods' => $NetsuitepaymentMethods, 'ShopifyshipmentMethods'=>$ShopifyshipmentMethods, 'NetsuiteshipmentMethods'=>$NetsuiteshipmentMethods]);
    }

    public function mappingupdateAction() {
        $shopifyModel = $this->loadModel('ShopifyModel');
        $settings = $shopifyModel->getSettingsByUserId(1); // Example user ID
        $this->render('settings/shopify.php', ['settings' => $settings]);
    }

    /**
     * Show Shopify settings page
     */
    public function indexAction() {
        $shopifyModel = $this->loadModel('ShopifyModel');
        $settings = $shopifyModel->getSettingsByUserId(1); // Example user ID
        $this->render('settings/shopify.php', ['settings' => $settings]);
    }

    /**
     * Update Shopify settings
     */

    public function updateAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $shopifyModel = $this->loadModel('ShopifyModel');
            $result = $shopifyModel->updateSettings($_POST['userId'], $_POST['domainName'], $_POST['apiKey'], $_POST['apiSecretKey'], $_POST['webhook']);
            if ($result) {
                $this->redirect('/settings/shopify');
            } else {
                $this->redirect('/settings/shopify?error=Unable to update settings');
            }
        } else {
            $this->redirect('/settings/shopify');
        }
    }

    public function handleWebhookAction() {
            $this->logWebhookEvent("Test.");
        $data = file_get_contents('php://input');
        $topic = $_SERVER['HTTP_X_SHOPIFY_TOPIC'] ?? 'unknown_topic';
        $hmacHeader = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';
        $verified = $this->verifyShopifyWebhook($data, $hmacHeader);

        $logMessages = [];
        $logMessages[] = "Webhook start";
        $logMessages[] = "Topic: " . $topic;

        // Load the webhook queue model
        $queueModel = $this->loadModel('ShopifyWebhookQueueModel');

        if ($verified) {
            // Insert the webhook request into the queue table.
            $queueModel->saveWebhookQueue($topic, $data, implode(PHP_EOL, $logMessages));
        } else {
            http_response_code(403);
            $this->logWebhookEvent("Webhook verification failed.");
            return;
        }

        // Return quickly to acknowledge the webhook receipt.
        http_response_code(200);
        echo "Webhook received and queued";
    }

    public function handleWebhookCronJobAction() {
        // Load the webhook queue model
        $queueModel = $this->loadModel('ShopifyWebhookQueueModel');
        $pendingWebhooks = $queueModel->getPendingWebhooks();

        // Loop through each pending webhook request.
        foreach ($pendingWebhooks as $webhookRecord) {
            $topic = $webhookRecord['topic'];
            $data = $webhookRecord['data'];
            // Convert log messages back to an array if needed or start a new array.
            $logMessages = ["Processing webhook ID " . $webhookRecord['id']];

            // Process based on topic.
            if ($topic == 'orders/create') {
                $this->createNetSuiteSalesOrderFromShopify($data, $topic, $logMessages);
            } elseif ($topic == 'orders/delete') {
                // ... process orders/delete if needed.
            } elseif ($topic == 'orders/updated') {
                // ...
            } elseif ($topic == 'products/create') {
                $payload = json_decode($data);
                if (isset($payload->variants)) {
                    foreach ($payload->variants as $variant) {
                        $this->createNetSuiteItem($variant, $payload->title, $payload->body_html, $payload->vendor, $topic, $logMessages);
                    }
                }
            } elseif ($topic == 'products/update') {
                $payload = json_decode($data);
                if (isset($payload->variants)) {
                    foreach ($payload->variants as $variant) {
                        $this->updateNetSuiteItem($variant, $payload->title, $payload->body_html, $payload->vendor, $topic, $logMessages);
                    }
                }
            }
            // Add additional topic handling as needed.

            // Mark the webhook as processed
            $queueModel->markAsProcessed($webhookRecord['id']);
        }
        
        // Optionally, output a summary.
        echo "Cron job completed processing " . count($pendingWebhooks) . " webhook(s).";
    }

    public function createNetSuiteSalesOrderFromShopify($shopifyOrderJson, $topic, $logMessages) {

        // SO244356
        // echo "<pre>";
        // print_r($this->getSalesOrderByInternalId('4691582'));
        // echo "</pre>";
        // exit();
        $webhookModel = $this->loadModel('ShopifyWebhookModel');

        $orderData = json_decode($shopifyOrderJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $logMessages[] = "JSON Decode Error: " . json_last_error_msg();
            $webhookId = $webhookModel->saveWebhook($topic, $shopifyOrderJson, implode("\n", $logMessages));
            return;
        }
        $service = $this->netsuiteService;
        
        $salesOrder = new SalesOrder();
        
        // Map external order ID (using Shopify order id)
        $salesOrder->externalId = (string)$orderData->id;
        
        $email = $orderData->customer->email ?? $orderData->contact_email;
        $firstName = $orderData->customer->first_name ?? '';
        $lastName = $orderData->customer->last_name ?? '';

        // Get the NetSuite customer internal ID (or create one if it doesn't exist)
        $customerInternalId = $this->getOrCreateCustomer($email, $firstName, $lastName);
        if (!$customerInternalId) {
            $logMessages[] = "Customer could not be found or created for email: " . $email;
            $webhookId = $webhookModel->saveWebhook($topic, $shopifyOrderJson, implode("\n", $logMessages));
            return;
        }

        $customerRef = new RecordRef();
        $customerRef->internalId = $customerInternalId;
        $salesOrder->entity = $customerRef;
        
        // Map transaction date using the Shopify order "created_at" field
        $salesOrder->trandate = date("Y-m-d", strtotime($orderData->created_at));
        
        // Map currency – assume USD internalId is "1"
        $currencyRef = new RecordRef();
        $currencyRef->internalId = "1";
        $salesOrder->currency = $currencyRef;
        
        // Build Billing Address string (if available)
        if (isset($orderData->billing_address)) {
            $billing = $orderData->billing_address;
            // Construct a multi-line address string
            $billAddress = trim(
                ($billing->first_name ?? '') . ' ' . ($billing->last_name ?? '') . "\n" .
                ($billing->address1 ?? '') . "\n" .
                ($billing->city ?? '') . ', ' . ($billing->province ?? '') . ' ' . ($billing->zip ?? '') . "\n" .
                ($billing->country ?? '')
            );
            $salesOrder->billAddress = $billAddress;
        }
        
        // Build Shipping Address string (if available)
        if (isset($orderData->shipping_address)) {
            $shipping = $orderData->shipping_address;
            $shipAddress = trim(
                ($shipping->first_name ?? '') . ' ' . ($shipping->last_name ?? '') . "\n" .
                ($shipping->address1 ?? '') . "\n" .
                ($shipping->city ?? '') . ', ' . ($shipping->province ?? '') . ' ' . ($shipping->zip ?? '') . "\n" .
                ($shipping->country ?? '')
            );
            $salesOrder->shipAddress = $shipAddress;
        }
        
        // Map line items from Shopify order "line_items" array
        $salesOrderItems = [];
        $parenttotalDiscount = 0;
        $grandTotal = 0;


        if (isset($orderData->line_items) && is_array($orderData->line_items)) {
            foreach ($orderData->line_items as $lineItem) {
                $soItem = new SalesOrderItem();

                // Look up the NetSuite internal item ID by SKU.
                $itemInternalId = $this->getItemInternalIdBySKU($lineItem->sku);
                if (!$itemInternalId) {
                    $logMessages[] = "Item not found for SKU: " . $lineItem->sku;
                    continue;
                }
                $itemRef = new RecordRef();
                $itemRef->internalId = $itemInternalId;
                $soItem->item = $itemRef;

                // Retrieve price and quantity (as floats for calculation)
                $price = floatval($lineItem->price);
                $quantity = floatval($lineItem->quantity);
                
                // Calculate the line total and add to grand total
                $lineTotal = $price * $quantity;
                $grandTotal += $lineTotal;
                
                // Sum all discount allocations for the line item, if any
                $totalDiscount = 0;
                if (isset($lineItem->discount_allocations) && is_array($lineItem->discount_allocations)) {
                    foreach ($lineItem->discount_allocations as $discount) {
                        $discountAmount = floatval($discount->amount);
                        $totalDiscount += $discountAmount;
                        $parenttotalDiscount += $discountAmount;
                    }
                }
                
                // Optionally, you can adjust the rate for the sales order item
                // $effectiveRate = ($lineTotal - $totalDiscount) / $quantity;
                // $soItem->rate = $effectiveRate;
                
                $soItem->amount = $lineTotal;
                $soItem->quantity = $quantity;
                
                $salesOrderItems[] = $soItem;
            }
        }
        $itemList = new SalesOrderItemList();
        $itemList->item = $salesOrderItems;

        if ($grandTotal > 0) {
            $discountPercentage = ($parenttotalDiscount / $grandTotal) * 100;
        } else {
            $discountPercentage = 0;
        }

        $dynamicDiscountRate = '-'.number_format($discountPercentage, 2) . '%';

        $discountRef = new RecordRef();
        $discountRef->internalId = '10120';
        $salesOrder->discountItem = $discountRef;
        $salesOrder->discountRate = $dynamicDiscountRate;

        $salesOrder->itemList = $itemList;
        
        // Map shipping cost (from total_shipping_price_set)
        if (isset($orderData->total_shipping_price_set->shop_money->amount)) {
            $salesOrder->shippingCost = $orderData->total_shipping_price_set->shop_money->amount;
        }
        
        // Map tax total (from total_tax)
        // $salesOrder->taxTotal = $orderData->total_tax;
        
        // Map additional fields as needed (e.g., memo)
        $salesOrder->memo = "Shopify Order #" . $orderData->order_number;
        
        // (Optional) Map payment terms based on payment_gateway_names if needed.
        if (isset($orderData->payment_gateway_names) && in_array("Cash on Delivery (COD)", $orderData->payment_gateway_names)) {
            $paymentTermRef = new RecordRef();
            $paymentTermRef->internalId = "2"; // CreditCard Payment method
            $salesOrder->terms = $paymentTermRef;
        }

        $shippingMethodRef = new RecordRef();
        // 10820
        $shippingMethodRef->internalId = "4"; // Replace with the actual internal ID from NetSuite
        $salesOrder->shipMethod = $shippingMethodRef;

        $salesOrder->salesRep = new RecordRef();
        $salesOrder->salesRep->internalId = "75324";

        $iqRepField = new StringCustomFieldRef();
        $iqRepField->internalId = "5885";  // This is the internal ID of the field
        $iqRepField->scriptId = "custbody_rep_code_so";  // The script ID as defined in NetSuite
        $iqRepField->value = "23-01";  // Your desired value

        $customFieldList = new CustomFieldList();
        $customFieldList->customField = [$iqRepField];

        $salesOrder->customFieldList = $customFieldList;



        // $salesOrder->custbody_rep_code_so = "20-01";

        // Submit the Sales Order to NetSuite using an AddRequest.
        try {
            $addRequest = new AddRequest();
            $addRequest->record = $salesOrder;
            $addResponse = $service->add($addRequest);
            if ($addResponse->writeResponse->status->isSuccess) {
                $logMessages[] = "Sales Order created successfully with internalId: " .
                    $addResponse->writeResponse->baseRef->internalId;
            } else {
                $logMessages[] = "Failed to create Sales Order: " .
                    $addResponse->writeResponse->status->statusDetail[0]->message;
            }
            $webhookId = $webhookModel->saveWebhook($topic, $shopifyOrderJson, implode("\n", $logMessages));
        } catch (Exception $e) {
            $logMessages[] = "Exception while creating Sales Order: " . $e->getMessage();
            $webhookId = $webhookModel->saveWebhook($topic, $shopifyOrderJson, implode("\n", $logMessages));
        }
    }



    public function getSalesOrderByInternalId($internalId) {
        $service = $this->netsuiteService; // assuming this is already initialized
        $request = new \NetSuite\Classes\GetRequest();
        $request->baseRef = new \NetSuite\Classes\RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = "salesOrder"; // specify the record type

        try {
            $response = $service->get($request);
            if ($response->readResponse->status->isSuccess) {
                $salesOrder = $response->readResponse->record;
                echo "<pre>" . print_r($salesOrder, true) . "</pre>";
            } else {
                echo "Error: " . $response->readResponse->status->statusDetail[0]->message;
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }

    

    private function getItemInternalIdBySKU($sku) {
        $service = $this->netsuiteService;

        // Build a search for the item using the SKU (assuming the SKU is stored in the itemId field)
        $search = new \NetSuite\Classes\ItemSearchBasic();
        $search->itemId = new \NetSuite\Classes\SearchStringField();
        $search->itemId->operator = "is";
        $search->itemId->searchValue = $sku;

        $searchRequest = new \NetSuite\Classes\SearchRequest();
        $searchRequest->searchRecord = $search;

        try {
            $searchResponse = $service->search($searchRequest);
            if (
                $searchResponse->searchResult->status->isSuccess &&
                isset($searchResponse->searchResult->recordList->record[0])
            ) {
                $record = $searchResponse->searchResult->recordList->record[0];
                return $record->internalId;
            }
        } catch (\Exception $e) {
            $this->logWebhookEvent("Error searching for SKU ($sku): " . $e->getMessage());
        }
        return null;
    }
    private function getOrCreateCustomer($email, $firstName, $lastName) {
        $service = $this->netsuiteService;
        
        // First, try to search for the customer by email.
        $search = new \NetSuite\Classes\CustomerSearchBasic();
        $search->email = new \NetSuite\Classes\SearchStringField();
        $search->email->operator = "is";
        $search->email->searchValue = $email;
        
        $searchRequest = new \NetSuite\Classes\SearchRequest();
        $searchRequest->searchRecord = $search;
        
        try {
            $searchResponse = $service->search($searchRequest);
            if (
                isset($searchResponse->searchResult->status->isSuccess) &&
                $searchResponse->searchResult->status->isSuccess &&
                isset($searchResponse->searchResult->recordList->record[0])
            ) {
                $customerRecord = $searchResponse->searchResult->recordList->record[0];
                return $customerRecord->internalId;
            }
        } catch (\Exception $e) {
            $this->logWebhookEvent("Customer search error for email $email: " . $e->getMessage());
        }
        
        
        // Create a new Customer record and supply the required fields.
        $customer = new \NetSuite\Classes\Customer();
        $customer->email = $email;
        $customer->firstName = $firstName;
        $customer->lastName = $lastName;
        
        // Required: Company Name. For individuals, you can combine first and last names.
        $customer->companyName = trim(($firstName ? $firstName : '') . ' ' . ($lastName ? $lastName : 'Individual'));
        
        // Required: Subsidiary. (Adjust the internalId as needed for your account.)
        $subsidiary = new \NetSuite\Classes\RecordRef();
        $subsidiary->internalId = '2';
        $customer->subsidiary = $subsidiary;

        // Required: Phone. Use a dummy value if not provided.
        $customer->phone = "0000000000";

        // Required: Category. Supply a RecordRef with a valid internalId.
        $customerCategory = new \NetSuite\Classes\RecordRef();
        $customerCategory->internalId = '1'; // Replace '1' with the proper category internalId from your account
        $customer->category = $customerCategory;
        
        
        $addRequest = new \NetSuite\Classes\AddRequest();
        $addRequest->record = $customer;
        
        try {
            $addResponse = $service->add($addRequest);
            if ($addResponse->writeResponse->status->isSuccess) {
                $newCustomerId = $addResponse->writeResponse->baseRef->internalId;
                return $newCustomerId;
            }
        } catch (\Exception $e) {
            $this->logWebhookEvent("Exception while creating customer for email $email: " . $e->getMessage());
        }
        
        return null;
    }


    public function handleWebhookListAction() {
        // Load the webhook model.
        $webhookModel = $this->loadModel('ShopifyWebhookModel');
        
        // Get the current page from the query string (default to 1).
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 10; // Number of items per page

        // Get all webhooks (or, ideally, modify your model to support paginated queries).
        $allWebhooks = $webhookModel->getAllWebhooks();
        $total = count($allWebhooks);

        // Calculate pagination offsets.
        $offset = ($currentPage - 1) * $perPage;
        $webhooksPage = array_slice($allWebhooks, $offset, $perPage);

        // Pass pagination data to the view.
        $this->render('webhooks/list.php', [
            'webhooks'    => $webhooksPage,
            'total'       => $total,
            'currentPage' => $currentPage,
            'perPage'     => $perPage
        ]);
    }


    private function verifyShopifyWebhook($data, $hmacHeader) {
        $shopifyModel = $this->loadModel('ShopifyModel');
        $settings = $shopifyModel->getSettingsByUserId(1); // Example user ID
        $secret = $settings['webhook'];

        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        return hash_equals($hmacHeader, $calculatedHmac);
    }
    public function logWebhookEvent($message) {
        $logFile = __DIR__ . '/shopify_webhooks.log'; // Use an absolute path
        $currentTimestamp = date('Y-m-d H:i:s');
        $logMessage = $currentTimestamp . ' - ' . $message . PHP_EOL;
        echo $logMessage;
        echo '<br>';
        if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
            error_log("Failed to write to log file: $logFile");
        }
    }

}
