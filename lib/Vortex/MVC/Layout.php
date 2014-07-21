<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 18-Jul-2014
 */

namespace Vortex\MVC;

use Vortex\Config;
use Vortex\Logger;
use Vortex\MVC\View;

/**
 * Class Layout
 * @package Vortex\View
 */
class Layout extends View {
    const LAYOUT_SCRIPTS_FOLDER = '_layouts';

    private $view;
    private $isLayout;
    private $layouts = array();
    private $currentLayout;

    /**
     * Wraps a view with layout
     * @param View $view a content view
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @internal param string $name a name of a view template
     */
    public function __construct($view) {
        if (!($view instanceof View))
            throw new \InvalidArgumentException('Argument $view is not an instance of Vortex\MVC\View');
        $this->view = $view;

        $config = Config::getInstance();
        $this->isLayout = $config->view->layout->enabled(false);

        if ($this->isLayout) {
            $templates = $config->view->layout->templates;

            $dir = APPLICATION_PATH . '/views/' . Layout::LAYOUT_SCRIPTS_FOLDER . '/';
            for ($i = 0; $i < count($templates); $i++) {
                $layout = $dir . $templates[$i] . '.'
                    . Config::getInstance()->view->extension('tpl');

                if (file_exists($layout))
                    $this->layouts[] = $templates[$i];
            }

            if (count($this->layouts) == 0)
                throw new \UnexpectedValueException("No valid layouts were found in config");

            $this->setCurrentLayout($config->view->layout->default);
        }
    }

    /**
     * Returns rendered View object
     * @return string a view content
     */
    public function content() {
        return $this->view->render();
    }

    /**
     * Enables layouts
     */
    public function enableLayout() {
        $this->isLayout = true;
    }

    /**
     * Disables layouts
     */
    public function disableLayout() {
        $this->isLayout = false;
    }

    /**
     * Checks if layout system is enabled
     * @return bool
     */
    public function isEnabled() {
        return $this->isLayout;
    }

    /**
     * Returns a name of currently used layout
     * @return string name of layout
     */
    public function getCurrentLayout() {
        return $this->currentLayout;
    }

    /**
     * Sets a layout to use
     * @param string $currentLayout
     * @return bool if such layout was declared in config file
     */
    public function setCurrentLayout($currentLayout) {
        if (!in_array($currentLayout, $this->layouts))
            return false;
        $this->currentLayout = $currentLayout;
        return true;
    }

    /**
     * Renders a layout
     * @return string layout content
     */
    public function render() {
        if (!$this->isEnabled())
            return $this->content();

        $path = APPLICATION_PATH . '/views/' . Layout::LAYOUT_SCRIPTS_FOLDER . '/' . $this->getCurrentLayout() . '.'
            . Config::getInstance()->view->extension('tpl');
        return $this->ob_include($path);
    }
}