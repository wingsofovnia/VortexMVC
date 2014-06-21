<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov, Rostislav Khanyukov
 * Date: 19-May-14
 *
 * @package Vortex
 */

namespace Vortex;
use Vortex\Exceptions\FrontException;

class FrontController {
	private $config;
	private $request;
	private $response;
	
	public function __construct() {
        $this->request  = new Request();
        $this->response = new Response();
        $this->config = Config::getInstance();
	}

	/**
     * Run the application
     * @throws FrontException if controller doesn't exists (and PRODUCTION state = 0)
     */
    public function run() {
        ob_start();
        try {
            $this->runAction($this->request->getController(), $this->request->getAction());
        } catch (FrontException $e) {
            try {
                $this->runAction($this->config->controller->error, $this->config->action->error);
            } catch (FrontException $e) {
                throw $e;
            }
        }
        $content = ob_get_clean();
        $this->response->setBody($content);
        $this->response->sendPacket();
    }

	/**
     * Runs an action of controller
     * @param string $controller name of controller
     * @param string $action name of action
     * @throws FrontException if controller or action doesn't exists
     */
    private function runAction($controller, $action) {
        $controller .= 'Controller';
        $action .= 'Action';

        $controller = 'Application\Controllers\\' . $controller;
        $controller = new $controller($this->request, $this->response);

        if (is_callable(array($controller, $action)) == false)
            throw new FrontException('Action <' . $action . '> does\'t exists!');
        $controller->$action();
    }
}