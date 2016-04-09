<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 23-Jul-14
 * Time: 20:15
 */

namespace application\widgets;

use vortex\mvc\view\View;
use vortex\mvc\view\Widget;

class TestWidget extends Widget {

    /**
     * Processes a widget's logic
     */
    public function draw() {
        $view = View::factory('test');
        $view->data->text = 'Awesome box!';
        return $view;
    }
}