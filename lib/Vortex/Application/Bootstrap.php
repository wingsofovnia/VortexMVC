<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 14-Sep-14
 */

namespace Vortex\Application;
use Vortex\HTTP\Request;
use Vortex\HTTP\Response;

/**
 * Class Bootstrap provides an interface for child Bootstrap class,
 * methods of what will be called before firing controller's action
 * @package Vortex
 * @subpackage Application
 */
abstract class Bootstrap {

    /**
     * @var \Vortex\HTTP\Request
     */
    protected $request;

    /**
     * @var \Vortex\HTTP\Response
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