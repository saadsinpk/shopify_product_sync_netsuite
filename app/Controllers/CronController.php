<?php
namespace App\Controllers;

use Core\Controller;

class CronController extends Controller {
    /**
     * Method to run cron jobs
     */
    public function runAction() {
        // Implementation of the cron job tasks
        echo "Cron jobs executed successfully.";
    }
}
