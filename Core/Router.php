<?php
namespace Core;

class Router {
    private $routes = [];

    public function add($route, $params) {
        $this->routes[$route] = $params;
    }

    public function dispatch($url) {
        $url = parse_url($url, PHP_URL_PATH); // Normalize the URL

        if (!$this->match($url)) {
            // No route matched; handle 404
            $this->handleNotFound();
            return false;
        }

        foreach ($this->routes as $route => $params) {
            if ($route === $url) {
                if ($this->isProtectedRoute($params)) {
                    header('Location: /login');
                    exit;
                }

                $this->invokeControllerAction($params);
                return true;
            }
        }
    }

    private function handleNotFound() {
        header("HTTP/1.1 404 Not Found");
        include '../app/views/error/notFound.php';  // Ensure this path is correct
        exit;
    }

    private function isProtectedRoute($params) {
        return isset($params['protected']) && $params['protected'] && !isAuthenticated();
    }

    private function invokeControllerAction($params) {
        $controllerName = "App\\Controllers\\" . $params['controller'];
        $actionName = $params['action'] . 'Action';

        if (!class_exists($controllerName)) {
            throw new \Exception("Controller class $controllerName not found");
        }

        $controllerObject = new $controllerName();
        if (!method_exists($controllerObject, $actionName)) {
            throw new \Exception("Method $actionName not found in $controllerName");
        }

        $controllerObject->$actionName();
    }

    private function removeQueryStringVariables($url) {
        // This might be redundant now with the use of parse_url above
        if ($url != '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }

    private function match($url) {
        return array_key_exists($url, $this->routes);
    }

    protected function getNamespace() {
        return 'App\Controllers\\';
    }
}
