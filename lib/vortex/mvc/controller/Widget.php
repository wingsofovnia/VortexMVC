<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\controller;
use vortex\mvc\view\View;

/**
 * Class Widget is a simplified controller for a reusable widgets
 */
abstract class Widget {
    /**
     * @var \ArrayObject
     */
    public $data;

    public function __construct() {
        $this->data = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Draws a widget
     * @return View a widget's view
     */
    public abstract function draw();
}