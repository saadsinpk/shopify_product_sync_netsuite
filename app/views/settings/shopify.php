<?php $userId = $_SESSION['user_id'] ?? null; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopify Settings Configuration</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Make sure this path points to your actual CSS file -->
</head>
<body>
    <nav>
        <ul class="navbar">
            <li><a href="/profile">Profile</a></li>
            <li><a href="/settings/shopify">Shopify Settings</a></li>
            <li><a href="/settings/netsuite">NetSuite Settings</a></li>
            <li><a href="/logout">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Shopify Settings</h1>
        <p>Update your Shopify API settings below.</p>

        <!-- Form to update Shopify settings -->
        <form action="/settings/shopify/update" method="post">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($userId); ?>">
            <div class="form-group">
                <label for="domainName">Shopify Domain Name:</label>
                <input type="text" id="domainName" name="domainName" value="<?php echo $settings['domain_name']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="apiKey">Shopify API Key:</label>
                <input type="text" id="apiKey" name="apiKey" value="<?php echo $settings['api_key']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="apiSecretKey">Shopify API Secret Key:</label>
                <input type="text" id="apiSecretKey" name="apiSecretKey" value="<?php echo $settings['api_secret_key']; ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="webhook">Shopify Webhook:</label>
                <input type="text" id="webhook" name="webhook" value="<?php echo $settings['webhook']; ?>" class="form-control" required>
            </div>
            
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update Settings</button>
        </form>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Make sure this path points to your actual JavaScript file -->
</body>
</html>
