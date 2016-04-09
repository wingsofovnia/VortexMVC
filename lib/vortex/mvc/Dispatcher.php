<?php
/**
 * Project: rework-vortex
 * Author: superuser
 * Date: 09-Apr-16
 * Time: 19:47
 */

namespace vortex\mvc;


use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\controller\Controller;
use vortex\mvc\view\Layout;
use vortex\mvc\view\View;
use vortex\routing\Route;
use vortex\utils\Config;

class Dispatcher {
    private $request;
    private $response;
    private $route;

    public function __construct(Request $request, Response $response, Route $route) {
        $this->request = $request;
        $this->response = $response;
        $this->route = $route;
    }

    public function dispatch() {
        $controllerClass = $this->route->getController();
        if (!class_exists($controllerClass))
            throw new DispatcherException('Controller #{' . $controllerClass . '} does\'t exist!');

        /** @var $controllerObj Controller */
        $controllerObj = new $controllerClass($this->request, $this->response);

        $actionMethod = $this->route->getAction();
        if (is_callable(array($controllerObj, $actionMethod)) == false)
            throw new DispatcherException('Action #{' . $actionMethod . '} does\'t exist in ' . $controllerClass);

        $controllerObj->preDispatch();
        $actionResult = $controllerObj->$actionMethod();

        if ($actionResult instanceof View) {
            $config = Config::getInstance();
            if ($config->view->layout->enabled(false)) {
                $actionResult = new Layout($actionResult, $config->view->layout->templates, $config->view->layout->default);
                $actionResult = $actionResult->render();
            }
        }

        $controllerObj->postDispatch();
        $this->response->appendBody($actionResult);
    }
} 