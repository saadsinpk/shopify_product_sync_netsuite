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
            if ($_POST['newPassword'] !== $_POST['confirmNewPassword']) {
                $this->redirect('/profile?error=Passwords do not match');
                return;
            }

            $userModel = $this->loadModel('UserModel');
            
            // Verify current password
            $currentUser = $userModel->verifyUser($_SESSION['username'], $_POST['currentPassword']);
            if (!$currentUser) {
                $this->redirect('/profile?error=Incorrect current password');
                return;
            }
            
            // Proceed to change password
            $result = $userModel->changePassword($_SESSION['user_id'], $_POST['newPassword']); // Use session user ID and POST data
            if ($result) {
                // Optionally, add a success message or handle the session
                $this->redirect('/profile?success=Password updated successfully');
            } else {
                $this->redirect('/profile?error=Unable to update password');
            }
        } else {
            // Redirect back if not a POST request
            $this->redirect('/profile');
        }
    }
}
