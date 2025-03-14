<?php
namespace App\Models;

use Core\Database;

class ShopifyOrderModel {

    /**
     * Save a list of Shopify order IDs into the database.
     *
     * This method loops through the given order IDs and inserts them
     * into the shopify_orders table. It uses an "ON DUPLICATE KEY UPDATE"
     * clause to avoid inserting duplicates (assuming order_id is unique).
     *
     * @param array $orderIds List of Shopify order IDs.
     */
    public function saveOrderIds($orderIds) {
        $db = Database::getInstance();
        // Assuming the shopify_orders table has a UNIQUE index on order_id.
        $stmt = $db->prepare("INSERT INTO shopify_orders (order_id, processed) 
            VALUES (:order_id, 0)
            ON DUPLICATE KEY UPDATE order_id = order_id");
        foreach ($orderIds as $orderId) {
            $stmt->execute([
                'order_id' => $orderId,
            ]);
        }
    }

    /**
     * Retrieve a batch of pending orders.
     *
     * Pending orders are those with processed = 0.
     *
     * @param int $limit The number of orders to retrieve.
     * @return array An array of pending orders.
     */
    public function getPendingOrders($limit) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM shopify_orders WHERE processed = 0 ORDER BY id ASC LIMIT :limit");
        // Bind the limit parameter as an integer.
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark an order as updated.
     *
     * This method updates the record for the given order ID,
     * setting the processed flag to 1 and updating the last_updated timestamp.
     *
     * @param mixed $orderId The Shopify order ID.
     * @param string $updatedAt The updated_at timestamp from Shopify.
     * @return int The number of rows updated.
     */
    public function markOrderAsUpdated($orderId, $updatedAt) {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE shopify_orders 
            SET processed = 1, last_updated = :updated_at 
            WHERE order_id = :order_id");
        $stmt->execute([
            'order_id'   => $orderId,
            'updated_at' => $updatedAt,
        ]);
        return $stmt->rowCount();
    }
}
