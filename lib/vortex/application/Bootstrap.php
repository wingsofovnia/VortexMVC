<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 14-Sep-14
 */

namespace vortex\application;
use vortex\http\Request;
use vortex\http\Response;

/**
 * Class Bootstrap provides an interface for child Bootstrap class,
 * methods of what will be called before firing controller's action
 */
abstract class Bootstrap {

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
     * Runs all init methods of bootstrapper
     */
    public function process() {
        $methods = get_class_methods($this);

        forEach ($methods as $method) {
            if (0 === strpos($method, 'init')) {
                echo $this->{$method}();
            }
        }
    }
} 