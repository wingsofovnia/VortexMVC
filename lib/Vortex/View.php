<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:08
 */

class Vortex_View {
    public $path;
    public $data;

    public function __construct($name) {
        $this->data = new Vortex_Registry();
        $path = APPLICATION_PATH . '/views/' . ucfirst(strtolower($name)) . 'View';
        $path .= '.' . Vortex_Config::getInstance()->getViewExtension();
        if (!file_exists($path))
            throw new Vortex_Exception_ViewError("View don't exists!");
        $this->path = $path;
    }

    public function render() {
        $params = $this->data->getVars();
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        include $this->path;
    }
} 