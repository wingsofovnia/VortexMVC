<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 18-Jul-2014
 */

namespace vortex\mvc\view;
use vortex\utils\Config;

/**
 * Class Layout
 */
class Layout extends View {
    const LAYOUT_SCRIPTS_FOLDER = '/views/layouts/';

    private $content;
    private $isLayout;
    private $layouts = array();
    private $currentLayout;

    /**
     * Wraps a view with layout
     * @param View|string $content a content
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @internal param string $name a name of a view template
     */
    public function __construct($content) {
        if ($content instanceof View)
            $content = $content->render();

        $this->content = $content;

        $this->scripts = APPLICATION_PATH . Layout::LAYOUT_SCRIPTS_FOLDER;

        $config = Config::getInstance();
        $this->isLayout = $config->view->layout->enabled(false);

        if ($this->isLayout) {
            $templates = $config->view->layout->templates;

            for ($i = 0; $i < count($templates); $i++) {
                $layout = $this->getTemplatePath($templates[$i]);

                if (file_exists($layout))
                    $this->layouts[] = $templates[$i];
            }

            if (count($this->layouts) == 0)
                throw new \UnexpectedValueException("No valid layouts were found in config");

            $this->setCurrentLayout($config->view->layout->default);
        }
    }

    /**
     * Disables layouts
     */
    public function disableLayout() {
        $this->isLayout = false;
    }

    /**
     * Enables layouts
     */
    public function enableLayout() {
        $this->isLayout = true;
    }

    /**
     * Renders a layout
     * @return string layout content
     */
    public function render() {
        $path = $this->getTemplatePath($this->getCurrentLayout());
        return $this->ob_include($path);
    }

    /**
     * Checks if layout system is enabled
     * @return bool
     */
    public function isEnabled() {
        return $this->isLayout;
    }

    /**
     * Returns rendered View object
     * @return string a view content
     */
    public function content() {
        return $this->content;
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
}