<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace application\controllers;

use vortex\mvc\controller\AController;

class IndexController extends AController {
    public function indexAction() {
        //$this->response->setHeader('Content-Type', 'text/plain');
        $this->view->data->firstWords = 'Hello World! It\'s a indexAction!';
        $this->view->setTemplate('index/index');
    }

    public function indexPOSTAction() {
        $this->view->setTemplate('index/index');
        $this->view->data->firstWords = 'Hello World!  It\'s a indexPOSTAction!';
    }
}