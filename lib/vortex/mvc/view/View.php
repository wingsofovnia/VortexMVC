<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\view;
use vortex\mvc\Controller\AWidget;
use vortex\utils\Config;

/**
 * Class View
 * This class is responsible for web application view
 */
class View {
    const VIEW_SCRIPTS_FOLDER = 'templates';
    public $data;
    protected $path;
    protected $scripts;
    private $noRender = false;

    /**
     * Init constuctor
     */
    public function __construct() {
        $this->data = new \ArrayObject();
        $this->scripts = APPLICATION_PATH . '/views/' . View::VIEW_SCRIPTS_FOLDER . '/';
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
        $script = $this->getScriptPath(strtolower($template));
        $this->path = $script;
    }

    protected function getScriptPath($view) {
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
        $path = $this->getScriptPath($view);

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
        @include (string)$target;
        return ob_get_clean();
    }

    /**
     * Renders an widget
     * @param string $widget a widget name
     * @throws \Vortex\Exceptions\ViewException
     * @internal param array $data additional data
     * @return string rendered widget
     */
    public function widget($widget) {
        $widget = ucfirst(strtolower($widget));
        $widget = 'Application\Controllers\\' . AWidget::WIDGET_CONTROLLERS_NAMESPACE . '\\' . $widget . 'Widget';
        if (!class_exists($widget))
            throw new ViewException('Widget #{' . $widget . '} does\'t exists!');

        /** @var $widgetObj AWidget */
        $widgetObj = new $widget();
        $widgetObj->render();
        return $widgetObj->getView()->render();
    }

    /**
     * Renders the whole view
     * @throws \Vortex\Exceptions\ViewException
     * @return string rendered view
     */
    public function render() {
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

    /**
     * Checks if noRender enabled
     * @return bool true, if noRender == false
     */
    public function isRenderable() {
        return !$this->noRender;
    }
}