<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex;

/**
 * Class Vortex_Controller
 * A general class of all controllers
 */
abstract class Controller {
    protected $request;
    protected $response;
    protected $config;

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
    public function init() {}

    /**
     * A must-have action for all controllers
     */
    abstract public function indexAction();
}