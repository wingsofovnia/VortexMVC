<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace Application\Controllers;

use Vortex\MVC\Controller\Controller;

class ErrorController extends Controller {
    public function indexAction() {
        $this->view->data->code = $this->request->getParam('code');
        $this->view->data->message = $this->request->getParam('message');
    }
}