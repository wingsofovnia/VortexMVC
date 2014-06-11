<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:08
 */

namespace Vortex;
use Vortex\Exceptions\ViewException;

/**
 * Class Vortex_View
 * This class is responsible for web application view
 */
class View {
    private $isLayout;
    private $layouts;
    private $currentLayout;
    private $path;
    private $config;
    public $data;

    /**
     * Init constuctor
     * @param string $name a name of a view template
     * @throws ViewException
     */
    public function __construct($name) {
        $this->data = new Registry();
        $this->config = Config::getInstance();
        $path = APPLICATION_PATH . '/views/' . ucfirst(strtolower($name)) . 'View';
        $path .= '.' . $this->config->view->extension('tpl');
        if (!file_exists($path))
            throw new ViewException("View don't exists!");
        $this->path = $path;

        $this->isLayout = $this->config->view->layout->enabled(false);
        $this->layouts = $this->config->view->layout->templates;
        $this->currentLayout = $this->config->view->layout->default;
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
     * @throws ViewException if partial doesn't exist
     */
    public function partial($view, $data = array()) {
        $path = APPLICATION_PATH . '/views/' . $view;
        if (strpos($view, 'layouts') !== 0)
            $path .= 'View';
        $path .= '.' . $this->config->view->extension('tpl');

        if (!is_file($path))
            throw new ViewException('Partial <' . $view . '> doesn\'t exist!');
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
            $path .= '.' . $this->config->view->extension('tpl');
            include $path;
        }
    }
}