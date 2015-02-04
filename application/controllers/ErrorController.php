<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace application\controllers;

use vortex\mvc\controller\AController;

class ErrorController extends AController {
    public function indexAction() {
        $this->view->data->code = $this->request->getParam('code');
        $this->view->data->message = $this->request->getParam('message');
    }
}