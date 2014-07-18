<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Application\Controllers;

use Vortex\MVC\Controller;
use Vortex\MVC\View;

class IndexController extends Controller {
    /**
     * @RequestMapping('/customMapping', 'GET');
     * @Redirect('index', 'index');
     * @PermissionLevels('-1');
     */
    public function indexAction() {
        //$this->response->setHeader('Content-Type', 'text/plain');
        $this->view->data->firstWords = 'Hello World! It\'s a indexAction!';
    }

    /**
     * @RequestMapping('/customMapping', 'POST');
     * @Redirect('index', 'index');
     * @PermissionLevels('0','1');
     */
    public function indexPOSTAction() {
        $view = new View('index\index');
        $view->data->firstWords = 'Hello World!  It\'s a indexPOSTAction!';
    }
}