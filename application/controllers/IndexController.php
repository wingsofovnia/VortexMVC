<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 20:34
 */

use Vortex\Controller;
use Vortex\View;

class IndexController extends Controller {

    public function indexAction() {
        //$this->response->setHeader('Content-Type', 'text/plain');
        $view = new View('index');
        $view->data->firstWords = 'Hello World!';
        $view->render();
    }
}