<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov, Rostislav Khanyukov
 * Date: 19-May-14
 */

namespace vortex\mvc\controller;

use vortex\components\auth\Auth;
use vortex\mvc\view\Layout;
use vortex\mvc\view\View;
use vortex\utils\Config;

class FrontController {
    private $config;
    private $request;
    private $response;

    public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->config = Config::getInstance();
    }

    /**
     * Run the application
     * @throws FrontException if controller doesn't exists (and PRODUCTION state = 0)
     */
    public function run() {
        ob_start();

        try {
            $this->redirect($this->request->getController(), $this->request->getAction());
        } catch (FrontException $e) {
            if (!Config::isProduction())
                throw $e;
            try {
                $this->request->addParam("exception", $e);
                $this->request->addParam('code', $e->getCode());
                $this->request->addParam('message', $e->getMessage());

                $this->redirect($this->config->controller->error, $this->config->action->error);
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
     * @throws FrontException if controller or action doesn't exists, or permission denied
     */
    private function redirect($controller, $action) {
        $_controller = $controller . 'Controller';
        $_action = $action . 'Action';

        $_controller = 'application\controllers\\' . ucfirst($_controller);
        if (!class_exists($_controller))
            throw new FrontException('Controller #{' . $_controller . '} does\'t exists!');

        $_controller = new $_controller($this->request, $this->response);

        if (is_callable(array($_controller, $_action)) == false)
            throw new FrontException('Action #{' . $_action . '} does\'t exists!');

        $actionPermissions = $this->request->getPermissions();
        if (count($actionPermissions) && $this->config->components->auth->enabled(true)) {
            $userPermissionLevel = Auth::getUserLevel();
            if (count($actionPermissions) > 0 && !in_array($userPermissionLevel, $actionPermissions))
                throw new FrontException('No permission for Controller#{' . get_class($_controller) . '}, Action#{' . $_action . '}!');
        }

        $_controller->setView(View::factory($controller . '/' . $action));
        $_controller->init();
        $_controller->$_action();

        if ($_controller == null)
            return;

        $layout = new Layout($_controller->getView());
        echo $layout->render();
    }
}