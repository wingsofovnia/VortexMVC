<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace Vortex\MVC\Controller;
use Vortex\HTTP\Request;
use Vortex\HTTP\Response;
use Vortex\MVC\View\View;
use Vortex\Utils\Config;

/**
 * Class Vortex_Controller
 * A general class of all controllers
 * @package Vortex
 * @subpackage MVC
 */
abstract class Controller {
    /**
     * @var \Vortex\HTTP\Request
     */
    protected $request;

    /**
     * @var \Vortex\HTTP\Response
     */
    protected $response;

    /**
     * @var \Vortex\Utils\Config
     */
    protected $config;

    /**
     * @var \Vortex\MVC\View\View
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