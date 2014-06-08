<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Router
 * This class parses a URL and routes the application.
 * In other words, decide what controller should be called.
 */
class Vortex_Router {
    private $controller;
    private $action;
    private $params;
    private $url;
    private $routes;
	private $config;
	private $request;
    private $response;
	
    /**
     * Inits defaults
     */
    public function __construct() {
	    $this->request = new Vortex_Request();
        $this->response = new Vortex_Response();
        $this->controller = Vortex_Config::getInstance()->controller->default;
        $this->action = Vortex_Config::getInstance()->action->default;
        $this->params = array();
        $this->url = '';
        $this->routes = array();
    }
    
	/**
     * Run the application
     * @throws Vortex_Exception_ControllerError if controller doesn't exists (and PRODUCTION state = 0)
     */
    public function run() {
        ob_start();
        $this->initConfigs();
        $this->initRouter();
        $this->request->addParams($this->getParams());
        try {
            $this->initAction($this->getController(), $this->getAction());
        } catch (Vortex_Exception_InitError $e) {
            try {
                $this->initAction($this->config->controller->error, $this->config->action->error);
            } catch (Vortex_Exception_InitError $e) {
                throw $e;
            }
        }
        $content = ob_get_clean();
        $this->response->setBody($content);
        $this->response->sendPacket();
    }
	
	/**
     * Includes application.php configs
     */
    private function initConfigs() {
        $this->config = Vortex_Config::getInstance();
    }
    
	/**
     * Inits router configs and parsing URL
     */
    private function initRouter() {
        $this->setUrl($_SERVER['REQUEST_URI']);
        $routes = $this->config->routes;
        foreach ($routes as $route) {
            $this->registerRoute((array)$route);
        }
        $this->parse();
    }
	
	/**
     * Runs an action of controller
     * @param string $controller name of controller
     * @param string $action name of action
     * @throws Vortex_Exception_InitError if controller or action doesn't exists
     */
    private function initAction($controller, $action) {
        $controller .= 'Controller';
        $controllerPath = APPLICATION_PATH . '/controllers/' . $controller . '.php';
        if (!file_exists($controllerPath))
            throw new Vortex_Exception_InitError('Controller does\'t exists!');
        require_once $controllerPath;
        $controller = new $controller($this->request, $this->response);
        $action .= 'Action';
        if (is_callable(array($controller, $action)) == false)
            throw new Vortex_Exception_InitError('Action does\'t exists!');
        $controller->$action();
    }
	
	/**
     * Process the url.
     * This method doesn't actually parse url, but decide if it
     * should be parsed or predefined route could be used.
     */
    public function parse() {
        if (!empty($this->url)) {
            if (isset($this->routes[$this->url])) {
                $this->controller = $this->routes[$this->url]['controller'];
                $this->action = $this->routes[$this->url]['action'];
                if (isset($this->routes[$this->url]['params']))
                    $this->params = $this->routes[$this->url]['params'];
            } else {
                $this->parseURL();
            }
        }
    }

    /**
     * Adds predefined route
     * @param array $route an assoc-array of url, controller & action
     */
    public function registerRoute($route) {
        $required = array('url', 'controller', 'action');
        if (count(array_intersect_key(array_flip($required), $route)) === count($required)) {
            $url = $route['url'];
            unset($route['url']);
            $this->routes[$url] = $route;
        }
    }

    /**
     * Deletes predefined route
     * @param string $url url to delete
     */
    public function unregisterRoute($url) {
        if (array_key_exists($url, $this->routes))
            unset($this->routes[$url]);
    }

    /**
     * Parses the URL
     */
    private function parseURL() {
        $args = explode('/', $this->url);
        $args = array_filter($args);
        $args = array_map('trim', $args);
        $args = array_map('strtolower', $args);
        $args = array_values($args);
        $num = count($args);
        if ($num > 0) {
            $this->controller = ucfirst($args[0]);
            if ($num > 1) {
                $this->action = $args[1];
                if ($num > 2) {
                    for ($i = 2; $i < $num; $i++) {
                        if (($i + 1) < $num) {
                            $this->params[$args[$i]] = $args[$i + 1];
                            $i++;
                        }
                    }
                }
            }
        }
    }

    /**
     * Gets a parsed name of controller
     * @return string name of controller
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Gets a parsed name of action
     * @return string action name
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Gets a parsed array of params
     * @return array a set of params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Sets an URL to parse
     * @param string $url an URL address
     */
    public function setUrl($url) {
        $this->url = $url;
        $this->cleanURL();
    }

    /**
     * Service method to clean URL from HOST name and special chars
     */
    private function cleanURL() {
        $url = urldecode($this->url);
        if (strpos($url, $_SERVER['SERVER_NAME']) !== false)
            $url =  substr($url, strpos($url, $_SERVER['SERVER_NAME']));
        if (strlen($url) > 1)
            $url = rtrim($url, '/');
        $url = preg_replace('/\s+/', '', $url);
        $this->url = preg_replace('/[^A-Za-z0-9\-]/', '', $url);
    }
} 