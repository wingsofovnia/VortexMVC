<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 13:57
 */

namespace vortex\backbone\vertebrae;

use vortex\backbone\VertebraException;
use vortex\backbone\VertebraInterface;
use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\controller\Controller;
use vortex\mvc\view\Layout;
use vortex\utils\Config;

class MVCVertebra implements VertebraInterface {
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function process(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;

        $controllerClass = $request->getController();
        $actionMethod = $request->getAction();

        $this->execute($controllerClass, $actionMethod);
    }

    private function execute($controllerClass, $actionMethod) {
        if (!class_exists($controllerClass))
            throw new VertebraException('Controller #{' . $controllerClass . '} does\'t exists!');

        /** @var $controller Controller */
        $controller = new $controllerClass($this->request, $this->response);

        if (is_callable(array($controller, $actionMethod)) == false)
            throw new VertebraException('Action #{' . $actionMethod . '} does\'t exists!');

        $controller->init();
        $actionResponse = $controller->$actionMethod();
        $actionResponse = $actionResponse->render();

        if (Config::getInstance()->view->layout->enabled(false)) {
            $actionResponse = new Layout($actionResponse);
            $actionResponse = $actionResponse->render();
        }

        $this->response->appendBody($actionResponse);
    }

}