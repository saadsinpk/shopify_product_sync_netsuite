<?php $userId = $_SESSION['user_id'] ?? null; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopify Settings Configuration</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Make sure this path points to your actual CSS file -->
    <style>
        table {
            width: 100%;
            text-align: center;
        }
        table td { 
            border: 1px solid;
        }
    </style>
</head>
<body>
    <nav>
        <ul class="navbar">
            <li><a href="/profile">Profile</a></li>
            <li><a href="/settings/shopify">Shopify Settings</a></li>
            <li><a href="/webhook_list">Logs</a></li>
            <li><a href="/settings/netsuite">NetSuite Settings</a></li>
            <li><a href="/logout">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Mapping Settings</h1>
        <p>Update your Shopify API settings below.</p>

        <div class="container" style="max-width: 1200px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background-color: #f9f9f9;">
            <form action="/settings/update" method="post">
                <!-- Payment Method Mapping -->
                <h2>Payment Method Mapping</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shopify Payment Method</th>
                            <th>NetSuite Payment Method</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ShopifypaymentMethods as $method): ?>
                            <tr>
                                <td><?= htmlspecialchars($method['name']); ?></td>
                                <td>
                                    <select name="paymentMapping[<?= htmlspecialchars($method['id']); ?>]" class="form-control">
                                        <?php foreach ($NetsuitepaymentMethods as $nsMethod): ?>
                                            <option value="<?= htmlspecialchars($nsMethod['id']); ?>"><?= htmlspecialchars($nsMethod['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn-edit">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Shipment Method Mapping -->
                <h2>Shipment Method Mapping</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shopify Shipment Method</th>
                            <th>NetSuite Shipment Method</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ShopifyshipmentMethods as $method): ?>
                            <tr>
                                <td><?= htmlspecialchars($method['name']); ?></td>
                                <td>
                                    <select name="shipmentMapping[<?= htmlspecialchars($method['id']); ?>]" class="form-control">
                                        <?php foreach ($NetsuiteshipmentMethods as $nsMethod): ?>
                                            <option value="<?= htmlspecialchars($nsMethod['id']); ?>"><?= htmlspecialchars($nsMethod['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn-edit">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Submit button -->
                <button type="submit" class="btn btn-primary">Update Settings</button>
            </form>
        </div>

    </div>

    <script src="/assets/js/script.js"></script> <!-- Make sure this path points to your actual JavaScript file -->
</body>
</html>
