<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Ensure this path points to your actual CSS file -->
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <p>Please enter your credentials to log in.</p>

        <!-- Display any flash messages if there are any -->
        <?php if (function_exists('flash')) flash('login_status'); ?>

        <!-- Form for logging in -->
        <form action="/login/process" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Log In</button>
        </form>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Ensure this path points to your actual JavaScript file -->
</body>
</html>
