<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\view;

/**
 * Class View is responsible for web application view
 * @package vortex\mvc\view
 */
class View {
    const TEMPLATE_POSTFIX = 'tpl';

    public $data;
    protected $template;

    public function __construct() {
        $this->data = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Creates a view
     * @param string $script view template name
     * @return \Vortex\MVC\View\View a cooked view object
     */
    public static function factory($script) {
        $view = new View();
        $view->setTemplate($script);
        return $view;
    }

    /**
     * Sets template to render
     * @param string $template a template script
     * @throws ViewException
     * @return View a new view object
     */
    public function setTemplate($template) {
        $this->template = $this->locateTemplate($template);
    }

    /**
     * Finds absolute path to template script
     * @param string $template tpl name
     * @return string absolute path
     */
    protected function locateTemplate($template) {
        return APP_TEMPLATES_PATH . DIRECTORY_SEPARATOR . strtolower($template) . '.' . View::TEMPLATE_POSTFIX;
    }

    /**
     * Renders another view template
     * @param string $template name of view template
     * @param array $data addition data for view template
     * @return string rendered partial
     * @throws ViewException if partial doesn't exist
     */
    public function partial($template, $data = array()) {
        $path = $this->locateTemplate($template);

        if (!is_file($path))
            throw new ViewException('Partial #{' . $path . '} doesn\'t exist!');

        foreach ($data as $key => $value)
            $this->data->$key = $value;

        return $this->ob_include($path);
    }

    /**
     * Makes include into variable
     * @param string $target what to include
     * @return string included content
     */
    protected function ob_include($target) {
        ob_start();
        include $target;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Renders an widget
     * @param string $widget a widget name
     * @throws ViewException
     * @internal param array $data additional data
     * @return string rendered widget
     */
    public function widget($widget) {
        $widget = ucfirst(strtolower($widget));
        $widget = APP_WIDGET_NAMESPACE . $widget . APP_WIDGET_POSTFIX;
        if (!class_exists($widget))
            throw new ViewException('Widget #{' . $widget . '} does\'t exists!');

        /** @var $widgetObj Widget */
        $widgetObj = new $widget();
        $view = $widgetObj->draw();
        return $view->render();
    }

    /**
     * Renders the whole view
     * @throws ViewException
     * @return string rendered view
     */
    public function render() {
        if (!file_exists($this->template))
            throw new ViewException('View #{' . $this->template . '} don\'t exists!');

        return $this->ob_include($this->template);
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        if (empty($data))
            throw new \InvalidArgumentException('Param $data should be not empty!');

        $this->data = $data;
    }
}