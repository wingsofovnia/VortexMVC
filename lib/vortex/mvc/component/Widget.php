<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\component;
use vortex\mvc\view\View;

/**
 * Class Widget is a simplified controller for a reusable widgets
 */
abstract class Widget {
    const WIDGET_CONTROLLERS_NAMESPACE = 'widgets';

    /**
     * @var \ArrayObject
     */
    public $data;
    protected $view;

    /**
     * Inits Widget object with default view according to widget class name
     */
    public function __construct() {
        $this->data = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
        $viewName = explode('\\', get_called_class());
        $viewName = array_pop($viewName);
        $viewName = strtolower(str_replace('Widget', '', $viewName));
        $this->view = View::factory($viewName);
        $this->view->setData($this->data);
    }

    /**
     * Processes a widget's logic
     */
    public abstract function draw();

    /**
     * Gets a View object
     * @return View object
     */
    public function getView() {
        return $this->view;
    }

    /**
     * Sets a View Object
     * @param View $view
     */
    public function setView($view) {
        $this->view = $view;
    }
}