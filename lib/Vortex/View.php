<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:08
 */

class Vortex_View {
    private $isLayout;
    private $layouts;
    private $currentLayout;
    private $path;
    public $data;

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

    public function setLayout($layout) {
        if (in_array($layout, $this->layouts))
            $this->currentLayout = $layout;
    }

    public function partial($view, $data = array()) {
        $path = APPLICATION_PATH . '/views/' . $view;
        if (strpos($view, 'layouts') !== 0)
            $path .= 'View';
        $path .= '.' . Vortex_Config::getInstance()->getViewExtension();

        if (!is_file($path))
            throw new Vortex_Exception_ViewError('Partial <' . $view . '> doesn\'t exists!');
        foreach ($data as $key => $value) {
            $this->data->$key = $value;
        }
        include $path;
    }

    public function render() {
        if ($this->isLayout)
            $this->layout();
        else
            $this->content();
    }

    private function content() {
        $params = $this->data->getVars();
        /*foreach ($params as $key => $value) {
            $$key = $value;
        }*/
        include $this->path;
    }

    private function layout() {
        if ($this->isLayout) {
            $path = APPLICATION_PATH . '/views/layouts/' . $this->currentLayout;
            $path .= '.' . Vortex_Config::getInstance()->getViewExtension();
            include $path;
        }
    }
} 