<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 23-Jul-14
 * Time: 20:15
 */

namespace application\controllers\widgets;

use vortex\mvc\component\Widget;

class TestWidget extends Widget {

    /**
     * Processes a widget's logic
     */
    public function draw() {
        $this->data->text = "Awesome box!";
    }
}