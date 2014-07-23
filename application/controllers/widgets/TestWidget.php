<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 23-Jul-14
 * Time: 20:15
 */

namespace Application\Controllers\Widgets;

use Vortex\Widget;

class TestWidget extends Widget {

    /**
     * Processes a widget's logic
     */
    public function render() {
        $this->data->text = "Awesome box!";
    }
}