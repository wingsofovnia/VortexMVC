<?php 
class Vortex_FrontController {
	private $config;
	private $request;
	private $response;

    private $asyncSpecialized = true;
	
	public function __construct() {
        $this->request  = new Vortex_Request();
        $this->response = new Vortex_Response();
        $this->config = Vortex_Config::getInstance();
	}

	/**
     * Run the application
     * @throws Vortex_Exception_ControllerError if controller doesn't exists (and PRODUCTION state = 0)
     */
    public function run() {
        ob_start();
        try {
            $this->runAction($this->request->getController(), $this->request->getAction());
        } catch (Vortex_Exception_InitError $e) {
            try {
                $this->runAction($this->config->controller->error, $this->config->action->error);
            } catch (Vortex_Exception_InitError $e) {
                throw $e;
            }
        }
        $content = ob_get_clean();
        $this->response->setBody($content);
        $this->response->sendPacket();
    }

    /**
     * Enables friendly action for XmlHttpRequests
     */
    public function enableAsyncAction() {
        $this->asyncSpecialized = true;
    }

    /**
     * Disables friendly action for XmlHttpRequests
     */
    public function disableAsyncAction() {
        $this->asyncSpecialized = false;
    }

	/**
     * Runs an action of controller
     * @param string $controller name of controller
     * @param string $action name of action
     * @throws Vortex_Exception_InitError if controller or action doesn't exists
     */
    private function runAction($controller, $action) {
        $controller .= 'Controller';
        $asyncAction = $action . 'AsyncAction';
        $action .= 'Action';

        $controllerPath = APPLICATION_PATH . '/controllers/' . $controller . '.php';
        if (!file_exists($controllerPath))
            throw new Vortex_Exception_InitError('Controller does\'t exists!');
        require_once $controllerPath;

        $controller = new $controller($this->request, $this->response);

        /* Checking if AsyncAction should be used */
        if ($this->asyncSpecialized && $this->request->isXMLHttpRequest())
            $action = $asyncAction;

        if (is_callable(array($controller, $action)) == false)
            throw new Vortex_Exception_InitError('Action <' . $action . '> does\'t exists!');
        $controller->$action();
    }
}