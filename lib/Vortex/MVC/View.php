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

    /**
     * Init constuctor
     * @param string $template a name of a view template file
     */
    private function __construct($template) {
        $this->data = new Registry();
        $this->path = $template;
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
            throw new ViewException('Partial <' . $view . '> doesn\'t exist!');
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
        $widget = 'Application\Controllers\Widgets\\' . $widget;
        if (!class_exists($widget))
            throw new ViewException('Widget #{' . $widget . '} does\'t exists!');

        $widgetObj = new $widget();
        $widgetObj->data->merge($data);
        return $widgetObj->render();
    }

    /**
     * Renders the whole view
     * @return string rendered view
     */
    public function render() {
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
     * Creates a view
     * @param string $viewTpl view name
     * @return \Vortex\MVC\View a cooked view object
     * @throws \Vortex\Exceptions\ViewException
     */
    public static function factory($viewTpl) {
        $path = APPLICATION_PATH . '/views/' . strtolower($viewTpl);
        $path .= '.' . Config::getInstance()->view->extension('tpl');

        if (!file_exists($path))
            throw new ViewException("View don't exists!");

        return new View($path);
    }
}