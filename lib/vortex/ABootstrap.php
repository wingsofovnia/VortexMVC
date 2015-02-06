<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 14-Sep-14
 */

namespace vortex;
use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\controller\FrontController;

/**
 * Class Bootstrap provides an interface for child Bootstrap class,
 * methods of what will be called before firing controller's action
 */
abstract class ABootstrap {

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
     */
    public final function __construct() {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Runs all init methods of bootstrapper
     */
    public function process() {
        $methods = get_class_methods($this);

        forEach ($methods as $method) {
            if (0 === strpos($method, 'init')) {
                echo $this->{$method}();
            }
        }

        /* Continues app lifecycle by running FrontController */
        $front = new FrontController($this->request, $this->response);
        $front->run();
    }
} 