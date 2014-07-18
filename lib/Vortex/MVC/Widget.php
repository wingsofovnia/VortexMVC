<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */
namespace Vortex\View;

use Vortex\Registry;

/**
 * Class Widget is a simplified controller for a reusable widgets
 * @package Vortex\View
 */
abstract class Widget {
    /**
     * @var \Vortex\Registry
     */
    public $data;

    public function __construct() {
        $this->data = new Registry();
    }

    /**
     * Renders a widget
     * @return string rendered widget
     */
    public abstract function render();
}