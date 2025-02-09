<?php

/**
 * Escapes HTML for output
 *
 * @param string $string The string to escape
 * @return string The escaped HTML
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirects to a specific URL
 *
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url", true, 303);
    exit;
}

/**
 * Hashes a password
 *
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Checks a plain text password against a hashed password
 *
 * @param string $password The plain text password
 * @param string $hash The hashed password
 * @return bool True if the passwords match, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Flash message helper to display one-time notifications.
 *
 * @param string $name Name of the flash message
 * @param string $message The message content (optional if getting a message)
 * @param string $class Bootstrap alert class (optional)
 * @return string|void Outputs the flash message HTML if retrieving, stores if setting
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!session_id()) session_start();

    // Store flash message
    if (!empty($name) && !empty($message)) {
        if (!empty($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    }
    // Display flash message
    elseif (!empty($name) && empty($message)) {
        if (!empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert alert-success';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}
