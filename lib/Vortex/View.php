<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:08
 */

/**
 * Class Vortex_View
 * This class is responsible for web application view
 */
class Vortex_View {
    private $isLayout;
    private $layouts;
    private $currentLayout;
    private $path;
    public $data;

    /**
     * Init constuctor
     * @param string $name a name of a view template
     */
    public function __construct($name) {
        $this->data = new Vortex_Registry();
        $path = APPLICATION_PATH . '/views/' . ucfirst(strtolower($name)) . 'View';
        $path .= '.' . Vortex_Config::getInstance()->getViewExtension();
        if (!file_exists($path))
            throw new Vortex_Exception_ViewError("View don't exists!");
        $this->path = $path;

        $this->isLayout = Vortex_Config::getInstance()->isLayouts();
        $this->layouts = Vortex_Config::getInstance()->getLayouts();
        $this->currentLayout = Vortex_Config::getInstance()->getDefaultLayout();
    }

    /**
     * Sets a name of layout, what will be used for rendering content
     * @param string $layout name of layout
     */
    public function setLayout($layout) {
        if (in_array($layout, $this->layouts))
            $this->currentLayout = $layout;
    }

    /**
     * Includes another view template
     * @param string $view name of view template
     * @param array $data addition data for view template
     * @throws Vortex_Exception_ViewError if partial doesn't exist
     */
    public function partial($view, $data = array()) {
        $path = APPLICATION_PATH . '/views/' . $view;
        if (strpos($view, 'layouts') !== 0)
            $path .= 'View';
        $path .= '.' . Vortex_Config::getInstance()->getViewExtension();

        if (!is_file($path))
            throw new Vortex_Exception_ViewError('Partial <' . $view . '> doesn\'t exist!');
        foreach ($data as $key => $value) {
            $this->data->$key = $value;
        }
        require $path;
    }

    /**
     * Renders a whole view template
     */
    public function render() {
        if ($this->isLayout)
            $this->layout();
        else
            $this->content();
    }

    /**
     * Renders a view template in layout
     */
    private function content() {
        require $this->path;
    }

    /**
     * Renders a layout
     */
    private function layout() {
        if ($this->isLayout) {
            $path = APPLICATION_PATH . '/views/layouts/' . $this->currentLayout;
            $path .= '.' . Vortex_Config::getInstance()->getViewExtension();
            include $path;
        }
    }
} 