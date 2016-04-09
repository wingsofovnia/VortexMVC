<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace application\controllers;

use vortex\mvc\controller\Controller;
use vortex\mvc\view\View;

class IndexController extends Controller {
    public function index() {
        $view = View::factory('index/index');
        $view->data->firstWords = 'Hello World! It\'s a indexAction!';
        return $view;
    }

    public function raw() {
        $this->response->body("Hello! It's a raw response. Layouting was skipped coz I dont return a View object. I'm useful for AJAX :)");
    }
}