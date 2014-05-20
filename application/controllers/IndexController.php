<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 20:34
 */

class IndexController extends Vortex_Controller {

    public function indexAction() {
        $model = new BeanModel();
        $model->testConnection();


        $view = new Vortex_View('index');
        $view->data->firstWords = 'Hello World!';
        $view->render();
    }

    public function didAction() {
        echo "oups!";
    }
}