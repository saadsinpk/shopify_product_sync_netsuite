<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\UserModel;

class ProfileController extends Controller {
    /**
     * Display user profile
     */
    public function indexAction() {
        // Assume user is logged in and their ID is stored in a session
        $this->render('profile/profile.php');
    }

    /**
     * Handle password change
     */
    public function changePasswordAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->loadModel('UserModel');
            $result = $userModel->changePassword($_SESSION['user_id'], $_POST['new_password']); // Use session user ID and POST data
            if ($result) {
                $this->redirect('/profile');
            } else {
                $this->redirect('/profile?error=Unable to update settings');
            }
        } else {
            // Handle incorrect access method with an error or redirect
            $this->redirect('/profile');
        }
    }
}
