<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cron Job Control Panel</title>
    <link rel="stylesheet" href="/assets/css/style.css"> <!-- Path to your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Cron Job Control Panel</h1>
        <p>Use this page to manually trigger cron jobs as needed.</p>

        <!-- Trigger Cron Job Button -->
        <form action="/cron/run" method="post">
            <button type="submit" class="btn btn-primary">Run Cron Job Now</button>
        </form>

        <!-- Display any flash messages -->
        <?php if (function_exists('flash')) flash('cron_message'); ?>

        <p><strong>Last Run Time:</strong> <?php echo date('Y-m-d H:i:s'); // Display current server time or last run time ?></p>
    </div>

    <script src="/assets/js/script.js"></script> <!-- Path to your JavaScript file -->
</body>
</html>
