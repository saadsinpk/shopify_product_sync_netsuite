<?php

namespace Core;

require_once '../vendor/autoload.php';


abstract class Controller {
    protected $route_params;

    public function __construct($route_params = []) {
        $this->route_params = $route_params;
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * as 'actionNameAction'.
     */
    public function __call($name, $args) {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before an action method.
     */
    protected function before() {
        // Code to be executed before action methods
    }

    /**
     * After filter - called after an action method.
     */
    protected function after() {
        // Code to be executed after action methods
    }

    /**
     * Method to load a model corresponding to the controller.
     */
    protected function loadModel($model) {
        $modelClass = 'App\\Models\\' . $model;
        if (class_exists($modelClass)) {
            return new $modelClass();
        } else {
            echo "Class does not exist in available paths.<br/>";
            throw new \Exception("Model class $modelClass not found.");
        }
    }



    /**
     * Method to render a view file
     */
    protected function render($view, $args = []) {
        extract($args, EXTR_SKIP);
        $file = BASE_PATH . "/app/views/$view";  // No additional .php here

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }



    /**
     * Redirect to a different page
     */
    protected function redirect($url) {
        header("Location: $url", true, 303);
        exit;
    }
}
