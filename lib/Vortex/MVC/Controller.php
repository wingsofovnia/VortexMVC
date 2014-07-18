<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex\MVC;

use Vortex\Config;
use Vortex\Request;
use Vortex\Response;

/**
 * Class Vortex_Controller
 * A general class of all controllers
 * @package Vortex\MVC
 */
abstract class Controller {
    protected $request;
    protected $response;
    protected $config;
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
        $this->init();
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