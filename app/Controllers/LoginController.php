<?php
namespace App\Controllers;

use Core\Controller;

class LoginController extends Controller {
    /**
     * Display the login page
     */
    public function indexAction() {
        if ($this->isAuthenticated()) {
            $this->redirect('/profile'); // Redirect if already logged in
        }
        $this->render('login/login.php');
    }

    /**
     * Handle the login process.
     */
    public function processAction() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->loadModel('UserModel');
            $user = $userModel->verifyUser($_POST['username'], $_POST['password']);
            if ($user) {
                session_regenerate_id(true); // Regenerate session ID
                $_SESSION['user_id'] = $user['id']; // Store user ID in session
                $_SESSION['username'] = $user['username']; // Store the username in session
                $_SESSION['is_authenticated'] = true; // Flag session as authenticated
                $this->redirect('/profile'); // Redirect to the profile page if successful
            } else {
                // Optionally use session to store error message
                $_SESSION['flash']['error'] = 'Invalid username or password';
                $this->redirect('/login');
            }
        } else {
            $this->redirect('/login');
        }
    }

    /**
     * Check if the user is already authenticated.
     */
    private function isAuthenticated() {
        return !empty($_SESSION['user_id']);
    }
    public function logoutAction() {
        session_start();
        $_SESSION = array(); // Clear all session variables
        session_destroy(); // Destroy the session
        setcookie(session_name(), "", time() - 42000); // Destroy the session cookie
        $this->redirect('/login'); // Redirect to login page
    }
}
