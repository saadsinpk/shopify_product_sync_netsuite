<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings</title>
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
        <h1>Profile Settings</h1>
        <p>Use the form below to change your password.</p>

        <!-- Form to update password -->
        <form action="/profile/changePassword" method="post">
            <div class="form-group">
                <label for="currentPassword">Current Password:</label>
                <input type="password" id="currentPassword" name="currentPassword" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmNewPassword">Confirm New Password:</label>
                <input type="password" id="confirmNewPassword" name="confirmNewPassword" class="form-control" required>
            </div>
            
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Ensure this path points to your actual JavaScript file -->
</body>
</html>
