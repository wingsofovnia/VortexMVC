<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex\MVC;

use Vortex\Config;
use Vortex\Exceptions\ViewException;
use Vortex\Registry;

/**
 * Class Vortex_View
 * This class is responsible for web application view
 * @package Vortex\MVC
 */
class View {
    public $data;
    protected $path;
    private $noRender;

    /**
     * Init constuctor
     */
    public function __construct() {
        $this->data = new Registry();
    }

    /**
     * Renders another view template
     * @param string $view name of view template
     * @param array $data addition data for view template
     * @return string rendered partial
     * @throws ViewException if partial doesn't exist
     */
    public function partial($view, $data = array()) {
        $path = APPLICATION_PATH . '/views/' . $view;
        $path .= '.' . Config::getInstance()->view->extension('tpl');

        if (!is_file($path))
            throw new ViewException('Partial #{' . $path . '} doesn\'t exist!');
        foreach ($data as $key => $value) {
            $this->data->$key = $value;
        }
        return $this->ob_include($path);
    }

    /**
     * Renders an widget
     * @param string $widget a widget name
     * @param array $data additional data
     * @return string rendered widget
     * @throws \Vortex\Exceptions\ViewException
     */
    public function widget($widget, $data = array()) {
        $widget = 'Application\Controllers\\' . Widget::WIDGET_CONTROLLERS_NAMESPACE . '\\' . $widget . 'Widget';
        if (!class_exists($widget))
            throw new ViewException('Widget #{' . $widget . '} does\'t exists!');

        /** @var $widgetObj Widget */
        $widgetObj = new $widget();
        $widgetObj->data->merge($data);
        $widgetObj->render();
        return $widgetObj->getView()->render();
    }

    /**
     * Enables rendering of view
     */
    public function enableRendering() {
        $this->noRender = false;
    }

    /**
     * Disables rendering of view
     */
    public function disableRendering() {
        $this->noRender = true;
    }

    /**
     * Renders the whole view
     * @return string rendered view
     */
    public function render() {
        if ($this->noRender)
            return null;
        return $this->ob_include($this->path);
    }

    /**
     * Makes include into variable
     * @param string $target what to include
     * @return string included content
     */
    protected function ob_include($target) {
        ob_start();
        @include (string)$target;
        return ob_get_clean();
    }

    /**
     * Change view script
     * @param string $template a template script
     * @throws \Vortex\Exceptions\ViewException
     * @return View a new view object
     */
    public function setTemplate($template) {
        $script = APPLICATION_PATH . '/views/' . strtolower($template);
        $script .= '.' . Config::getInstance()->view->extension('tpl');

        if (!file_exists($script))
            throw new ViewException("View don't exists!");

        $this->path = $script;
    }

    /**
     * Creates a view
     * @param string $script view template name
     * @return \Vortex\MVC\View a cooked view object
     * @throws \Vortex\Exceptions\ViewException
     */
    public static function factory($script) {
        $view = new View();
        $view->setTemplate($script);
        return $view;
    }
}