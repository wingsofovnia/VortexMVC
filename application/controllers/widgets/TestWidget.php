<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 23-Jul-14
 * Time: 20:15
 */

namespace application\controllers\widgets;

use vortex\mvc\controller\Widget;

class TestWidget extends Widget {

    /**
     * Processes a widget's logic
     */
    public function render() {
        $this->data->text = "Awesome box!";
    }
}