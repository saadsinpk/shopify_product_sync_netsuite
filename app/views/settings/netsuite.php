<?php $userId = $_SESSION['user_id'] ?? null; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NetSuite Settings Configuration</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Ensure this path points to your actual CSS file -->
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
        <h1>NetSuite Settings</h1>
        <p>Update your NetSuite API settings below.</p>

        <!-- Form to update NetSuite settings -->
        <form action="/settings/netsuite/update" method="post">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($userId); ?>">

            <div class="form-group">
                <label for="accountId">NetSuite Account ID:</label>
                <input type="text" id="accountId" name="accountId" value="<?php echo $settings['account_id']; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="tokenId">NetSuite Token ID:</label>
                <input type="text" id="tokenId" name="tokenId" value="<?php echo $settings['token_id']; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="tokenSecret">NetSuite Token Secret:</label>
                <input type="text" id="tokenSecret" name="tokenSecret" value="<?php echo $settings['token_secret']; ?>" class="form-control">
            </div>
            
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update Settings</button>
        </form>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Ensure this path points to your actual JavaScript file -->
</body>
</html>
