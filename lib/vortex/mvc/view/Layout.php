<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 18-Jul-2014
 */

namespace vortex\mvc\view;

/**
 * Class Layout provides a reusable view skeleton over all controllers' actions
 * @package vortex\mvc\view
 */
class Layout extends View {
    private $content;
    private $isLayout = true;
    private $layouts = array();
    private $currentLayout;

    public function __construct(View $view, $layouts, $currentLayout = NULL) {
        $this->content = $view->render();

        foreach ($layouts as $layout) {
            $layoutPath = $this->locateTemplate($layout);
            if (!file_exists($layoutPath))
                throw new \InvalidArgumentException("Can't locate layout " . $layout . " in " . $layoutPath);

            $this->layouts[] = $layout;
        }

        if (!empty($currentLayout)) {
            $currentLayoutPath = $this->locateTemplate($currentLayout);
            if (!file_exists($currentLayoutPath))
                throw new \InvalidArgumentException("Bad current layout param. File doesn't exist.");

            $this->setCurrentLayout($currentLayout);
        }
    }

    protected function locateTemplate($template) {
        return APP_LAYOUTS_PATH . DIRECTORY_SEPARATOR . strtolower($template) . '.' . View::TEMPLATE_POSTFIX;
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
     * Checks if layout system is enabled
     * @return bool
     */
    public function isEnabled() {
        return $this->isLayout;
    }

    /**
     * Renders a layout
     * @return string layout content
     */
    public function render() {
        $path = $this->locateTemplate($this->getCurrentLayout());
        return $this->ob_include($path);
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
     * @throws \InvalidArgumentException
     */
    public function setCurrentLayout($currentLayout) {
        if (!in_array($currentLayout, $this->layouts))
            throw new \InvalidArgumentException("Unknown layout name. Expected = " . implode(", ", $this->layouts));

        $this->currentLayout = $currentLayout;
    }
}