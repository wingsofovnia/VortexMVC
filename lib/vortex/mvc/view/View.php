<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\view;
use vortex\component\Widget;
use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class View
 * This class is responsible for web application view
 */
class View {
    const VIEW_SCRIPTS_FOLDER = '/views/templates/';
    public $data;
    protected $path;
    protected $scripts;

    /**
     * Init constuctor
     */
    public function __construct() {
        $this->data = new \ArrayObject(array(), \ArrayObject::ARRAY_AS_PROPS);
        $this->scripts = APPLICATION_PATH . View::VIEW_SCRIPTS_FOLDER;
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
     * Change view script
     * @param string $template a template script
     * @throws ViewException
     * @return View a new view object
     */
    public function setTemplate($template) {
        $script = $this->getTemplatePath(strtolower($template));
        $this->path = $script;
    }

    protected function getTemplatePath($view) {
        $extension = Config::getInstance()->view->extension('tpl');
        return $this->scripts . $view . '.' . $extension;
    }

    /**
     * Renders another view template
     * @param string $view name of view template
     * @param array $data addition data for view template
     * @return string rendered partial
     * @throws ViewException if partial doesn't exist
     */
    public function partial($view, $data = array()) {
        $path = $this->getTemplatePath($view);

        if (!is_file($path))
            throw new ViewException('Partial #{' . $path . '} doesn\'t exist!');
        foreach ($data as $key => $value) {
            $this->data->$key = $value;
        }
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
        $widget = 'application\controllers\\widgets\\' . $widget . 'Widget';
        if (!class_exists($widget))
            throw new ViewException('Widget #{' . $widget . '} does\'t exists!');
        /** @var $widgetObj Widget */
        $widgetObj = new $widget();
        $widgetObj->draw();
        return $widgetObj->getView()->render();
    }

    /**
     * Renders the whole view
     * @throws ViewException
     * @return string rendered view
     */
    public function render() {
        if (!file_exists($this->path))
            throw new ViewException('View #{' . $this->path . '} don\'t exists!');

        return $this->ob_include($this->path);
    }

    /**
     * @return \ArrayObject
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param \ArrayObject $data
     * @throws \InvalidArgumentException if param $data is empty
     */
    public function setData($data) {
        if (empty($data))
            throw new \InvalidArgumentException('Param $data should be not empty!');
        $this->data = $data;
    }

}