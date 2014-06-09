<?php 
class Vortex_FrontController {
	
	private $router;
	private $config;
	private $request;
	private $response;
	
	public function __construct() {
		$this->router   = new Vortex_Router();
        $this->request  = new Vortex_Request();
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
        $this->router->setUrl($_SERVER['REQUEST_URI']);
        $routes = $this->config->routes;
        foreach ($routes as $route) {
            $this->router->registerRoute((array)$route);
        }
        $this->router->parse();
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
}