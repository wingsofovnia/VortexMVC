<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

define('ROOT_PATH', realpath(dirname(__FILE__)));
define('LIB_PATH', ROOT_PATH . '/lib');
define('APPLICATION_PATH', ROOT_PATH . '/application');
require_once LIB_PATH . '/vortex/Application.php';

use vortex\Application;
Application::run();

/**
 * Application LifeCycle
 * ---------------------
 * Application::run()
 *  - set-up -> * Class Loader
 *              * Error Handlers
 *  - runs -> Bootstrap
 *             - builds -> * Request
 *                            - ask route -> Router
 *                                            -> ask -> Annotations
 *                                            -> determine -> * Controller
 *                                                            * Action
 *                                                            * Addition params from url
 *                            - parse -> * $_SESSION
 *                                       * $_COOKIES
 *                                       * $_POST
 *                                       * $_GET
 *                         * Response
 *             - runs -> * ininFunctions()
 *                       * FrontController (continues life-cycle)
 *                          - check -> Auth Permissions
 *                          - runs -> Controller
 *                                     - runs -> Action
 *                          - draws -> View
 *                          - sends -> Response
 */