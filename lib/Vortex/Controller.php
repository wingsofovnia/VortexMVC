<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Controller
 * A general class of all controllers
 */
abstract class Vortex_Controller {
    protected $request;
    protected $response;
    protected $config;

    /**
     * Init constructor
     * @param Vortex_Request $request a request wrapper
     * @param Vortex_Response $response a response wrapper
     */
    public final function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
        $this->config = Vortex_Config::getInstance();
        $this->init();
    }

    /**
     * A child quasi-constructor
     */
    public function init() {}

    /**
     * A must-have action for all controllers
     */
    abstract public function indexAction();
} 