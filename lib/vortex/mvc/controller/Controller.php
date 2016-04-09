<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\mvc\controller;
use vortex\http\Request;
use vortex\http\Response;
use vortex\utils\Annotation;

/**
 * Class Controller is a base class for all controllers
 * @package vortex\mvc\controller
 */
abstract class Controller {
    /**
     * @var \vortex\http\Request
     */
    protected $request;

    /**
     * @var \vortex\http\Response
     */
    protected $response;

    /**
     * Init constructor
     * @param Request $request a request wrapper
     * @param Response $response a response wrapper
     */
    public final function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Method is called by Dispatcher before calling an action
     */
    public function preDispatch() {
    }

    /**
     * Method is called by Dispatcher after calling an action and rendering a view
     */
    public function postDispatch() {
    }
}