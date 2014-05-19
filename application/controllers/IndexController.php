<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 20:34
 */

class IndexController extends Vortex_Controller {

    public function indexAction() {
        $view = new Vortex_View('index');
        $model = new BeanModel();
        $model->testConnection();
        $view->data->var = 'HELLO WORD!';
        $view->render();
    }

    public function didAction() {
        echo "oups!";
    }
}