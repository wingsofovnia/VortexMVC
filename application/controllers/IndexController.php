<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 20:34
 */

class IndexController extends Vortex_Controller {

    public function indexAction() {
        //$this->response->setHeader('Content-Type', 'text/plain');
        $view = new Vortex_View('index');
        $view->data->firstWords = 'Hello World!';
        $view->render();
    }
}