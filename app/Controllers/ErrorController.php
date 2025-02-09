<?php
namespace App\Controllers;

use Core\Controller;

class ErrorController extends Controller {
    /**
     * Action to handle not found errors (404)
     */
    public function notFoundAction() {
        // Set the HTTP response status code to 404
        http_response_code(404);

        // Render a not found view
        $this->render('error/notFound.php');
    }
}
