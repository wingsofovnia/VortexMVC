<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Application
 * This one prepare engine for MVC work, inits router and autoloader.
 * Also, this class operate with application output: it waits until MVC has
 * finished it's work, and then call Vortex_Response to send a packet.
 */
class Vortex_Application {
    private $router;
    private $request;
    private $response;
    private $config;
    
    /**
     * Init constructor
     */
    public function __construct() {
        $this->registerAutoLoader();
        $this->router = new Vortex_Router();
        $this->request = new Vortex_Request();
        $this->response = new Vortex_Response();
    }

    /**
     * Run the application
     * @throws Vortex_Exception_ControllerError if controller doesn't exists (and PRODUCTION state = 0)
     */
    public function run() {
        ob_start();
        $this->initConfigs();
        $this->initRouter();

        $this->request->addParams($this->router->getParams());
        try {
            $this->initAction($this->router->getController(), $this->router->getAction());
        } catch (Vortex_Exception_InitError $e) {
            try {
                $this->initAction($this->config->getErrorController(), $this->config->getErrorAction());
            } catch (Vortex_Exception_InitError $e) {
                throw $e;
            }
        }
        $content = ob_get_clean();
        $this->response->setBody($content);
        $this->response->sendPacket();
    }

    /**
     * Inits router configs and parsing URL
     */
    private function initRouter() {
        $this->router->setUrl($_SERVER['REQUEST_URI']);
        $routes = $this->config->getRoutes();
        for ($i = 0; $i < count($routes); $i++)
            $this->router->registerRoute($routes[$i]);
        $this->router->parse();
    }

    /**
     * Includes application.php configs
     */
    private function initConfigs() {
        include_once APPLICATION_PATH . '/application.php';
        $this->config = Vortex_Config::getInstance();
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
     * Registers AutoLoader with `spl_autoload_register`
     */
    private function registerAutoLoader() {
        spl_autoload_register(function ($classname) {
            /* Is it a lib class? */
            $libs = glob(LIB_PATH . '/*' , GLOB_ONLYDIR);
            $libs = array_map(function($path) {return basename($path);}, $libs);

            $regexp = '/^[';
            foreach ($libs as $lib)
                $regexp .= $lib . '|';
            $regexp = rtrim($regexp, '|') . ']/';
            $isLib = preg_match($regexp, $classname) > 0;

            if ($isLib) {
                $path = LIB_PATH . '/' . str_replace('_', DIRECTORY_SEPARATOR, $classname);
            } else {
                $appClass = $classname . 's';
                $appClass = preg_split('/(?=[A-Z])/', $appClass);
                $appClass = array_values(array_filter($appClass));

                $dir = end($appClass);
                $path = APPLICATION_PATH . '/' . $dir . '/' . $classname;
            }
            require_once($path . '.php');
        });
    }
}

