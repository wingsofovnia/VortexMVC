<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex;
use Vortex\Exceptions\ViewException;

/**
 * Class Vortex_View
 * This class is responsible for web application view
 * @package Vortex
 */
class View {
    const VIEW_SCRIPTS_FOLDER = 'templates';
    public $data;
    protected $path;
    protected $scripts;
    private $noRender;

    /**
     * Init constuctor
     */
    public function __construct() {
        $this->data = new Registry();
        $this->scripts = APPLICATION_PATH . '/views/' . View::VIEW_SCRIPTS_FOLDER . '/';
    }

    /**
     * Creates a view
     * @param string $script view template name
     * @return \Vortex\View a cooked view object
     * @throws \Vortex\Exceptions\ViewException
     */
    public static function factory($script) {
        $view = new View();
        $view->setTemplate($script);
        return $view;
    }

    /**
     * Change view script
     * @param string $template a template script
     * @throws ViewException
     * @return View a new view object
     */
    public function setTemplate($template) {
        $script = $this->getScriptPath(strtolower($template));
        $this->path = $script;
    }

    /**
     * Renders another view template
     * @param string $view name of view template
     * @param array $data addition data for view template
     * @return string rendered partial
     * @throws ViewException if partial doesn't exist
     */
    public function partial($view, $data = array()) {
        $path = $this->getScriptPath($view);

        if (!is_file($path))
            throw new ViewException('Partial #{' . $path . '} doesn\'t exist!');
        foreach ($data as $key => $value) {
            $this->data->$key = $value;
        }
        return $this->ob_include($path);
    }

    protected function getScriptPath($view) {
        $extension = Config::getInstance()->view->extension('tpl');
        return $this->scripts . $view . '.' . $extension;
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
     * Renders an widget
     * @param string $widget a widget name
     * @param array $data additional data
     * @return string rendered widget
     * @throws \Vortex\Exceptions\ViewException
     */
    public function widget($widget, $data = array()) {
        $widget = ucfirst(strtolower($widget));
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
     * Renders the whole view
     * @throws Exceptions\ViewException if view script doesn't exists
     * @return string rendered view
     */
    public function render() {
        if ($this->noRender)
            return null;
        if (!file_exists($this->path))
            throw new ViewException('View #{' . $this->path . '} don\'t exists!');

        return $this->ob_include($this->path);
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
}