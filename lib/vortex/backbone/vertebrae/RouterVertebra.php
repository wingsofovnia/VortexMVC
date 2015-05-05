<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 13:54
 */

namespace vortex\backbone\vertebrae;

use vortex\backbone\VertebraInterface;
use vortex\http\Request;
use vortex\http\Response;
use vortex\http\Router;

class RouterVertebra implements VertebraInterface {
    public function process(Request $request, Response $response) {
        $router = new Router($request);

        $routerController = $router->getController();
        $routerAction = $router->getAction();
        $urlParams = $router->getParams();

        $request->setController($routerController);
        $request->setAction($routerAction);
        $request->addParams($urlParams);
    }
}