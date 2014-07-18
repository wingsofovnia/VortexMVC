<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Application\Controllers;

use Vortex\Controller;
use Vortex\View;

class ErrorController extends Controller {
    public function indexAction() {
        $this->view->data->code = $this->request->getParam('code');
        $this->view->data->message = $this->request->getParam('message');
    }
}