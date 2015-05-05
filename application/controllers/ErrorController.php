<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace application\controllers;

use vortex\mvc\controller\Controller;

class ErrorController extends Controller {
    public function index() {
        $this->view->data->message = $this->request->getParam('message');
    }
}