<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\controller;
use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\view\View;
use vortex\utils\Annotation;
use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class Vortex_Controller
 * A general class of all controllers
 */
abstract class AController {
    /**
     * @var \vortex\http\Request
     */
    protected $request;

    /**
     * @var \vortex\http\Response
     */
    protected $response;

    /**
     * @var \vortex\utils\Config
     */
    protected $config;

    /**
     * @var \vortex\mvc\view\View
     */
    protected $view;

    /**
     * Init constructor
     * @param Request $request a request wrapper
     * @param Response $response a response wrapper
     */
    public final function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->config = Config::getInstance();
    }

    /**
     * A child controller quasi-constructor
     */
    public function init() {
    }

    /**
     * Gets a view object
     * @return View a View object
     */
    public function getView() {
        return $this->view;
    }

    /**
     * Sets a View object
     * @param View $view a View object
     * @throws \InvalidArgumentException
     */
    public function setView($view) {
        if (!($view instanceof View))
            throw new \InvalidArgumentException('Argument $view is not an instance of Vortex\MVC\View');
        $this->view = $view;
    }
}