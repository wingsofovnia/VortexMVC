<?php

/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:11
 */
class Vortex_Application {
    private $router;
    private $request;
    private $response;
    private $configs;

    public function __construct() {
        $this->registerAutoLoader();
        $this->router = new Vortex_Router();
        $this->request = new Vortex_Request();
        $this->response = new Vortex_Response();
    }

    public function run() {
        $this->initConfigs();
        $this->initRouter();

        $this->request->addParams($this->router->getParams());
        $controllerName = $this->router->getController() . 'Controller';
        $inc = APPLICATION_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($inc)) {
            if (!Vortex_Config::getInstance()->isProduction())
                throw new Vortex_Exception_ControllerError('Controller doesn\'t exists!');
            else {
                $controllerName = Vortex_Config::getInstance()->getDefaultController() . 'Controller';
                $inc = APPLICATION_PATH . '/controllers/' . $controllerName . '.php';
            }
        }
        Vortex_Logger::debug("INCLUDING CONTROLLER :: " . $inc);
        include $inc;
        $controller = new $controllerName($this->request, $this->response);
        $action = $this->router->getAction() . 'Action';
        if (is_callable(array($controller, $action)) == false)
            $action = Vortex_Config::getInstance()->getDefaultAction() . 'Action';
        $controller->$action();
    }

    private function initRouter() {
        $this->router->setUrl($_SERVER['REQUEST_URI']);
        $routes = Vortex_Config::getInstance()->getRoutes();
        for ($i = 0; $i < count($routes); $i++)
            $this->router->registerRoute($routes[$i]);
        $this->router->parse();
    }

    private function initConfigs() {
        include_once APPLICATION_PATH . '/application.php';
    }

    private function registerAutoLoader() {
        spl_autoload_register(function ($classname) {
            if (substr($classname, -strlen('Model')) === 'Model' && strpos($classname, '_') === false)
                $path = APPLICATION_PATH . '/models/' . $classname;
            else
                $path = LIB_PATH . '/' . str_replace('_', DIRECTORY_SEPARATOR, $classname);
            echo $path.'<br/>';
            require_once($path . '.php');
        });
    }

}

