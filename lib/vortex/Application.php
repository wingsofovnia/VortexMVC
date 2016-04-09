<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 09-Apr-16
 * Time: 19:24
 */

namespace vortex;

use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\Dispatcher;
use vortex\routing\Route;
use vortex\routing\Router;
use vortex\utils\Logger;
use vortex;

/**
 * Class Application is an entry point of VortexMVC that inits and boots it.
 * @package vortex
 */
class Application {
    const ROUTES_FILE_NAME = 'routes.cfg';

    private static $httpRequest;
    private static $httpResponse;
    private static $router;

    /**
     * Initializes Application class loader, handlers, router and http Request & Response
     */
    public static function init() {
        Application::registerClassLoader();
        Application::registerHandlers();

        Application::$httpRequest = new Request();
        Application::$httpResponse = new Response();

        Application::$router = new Router();
        $routesFile = new \SplFileObject(APP_PATH . DIRECTORY_SEPARATOR . self::ROUTES_FILE_NAME);
        Application::$router->parseRules($routesFile, true);
    }

    /**
     * Routes request to Dispatcher
     * @throws routing\RouterException if no route found
     */
    public static function dispatch() {
        /** @var $route Route */
        $route = Application::$router->route(Application::$httpRequest);
        if ($route === NULL)
            throw new vortex\routing\RouterException("No route defined for URL: " . Application::$httpRequest->getRawUrl());

        Application::$httpRequest->setRoute($route);
        $dispatcher = new Dispatcher(Application::$httpRequest, Application::$httpResponse, $route);
        $dispatcher->dispatch();
    }

    /**
     * Echoing a Http Request
     */
    public static function display() {
        Application::$httpResponse->send();
    }

    /**
     * Initialize Spl Class Loader
     */
    private static function registerClassLoader() {
        require LIB_PATH . '/vortex/utils/SplClassLoader.php';
        $libLoader = new \SplClassLoader('vortex', LIB_PATH);
        $libLoader->register();

        $appLoader = new \SplClassLoader('application', ROOT_PATH);
        $appLoader->register();
    }

    /**
     * Registers error handlers
     */
    private static function registerHandlers() {
        set_exception_handler(function ($e) {
            Logger::exception((string)$e);
        });
        set_error_handler(function ($code, $message, $file, $line) {
            Logger::error($code . ' : ' . $message . "\n" . $file . ' at line ' . $line);
        });
    }
} 