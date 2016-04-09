<?php
/**
 * Project: rework-vortex
 * Author: superuser
 * Date: 09-Apr-16
 * Time: 19:24
 */

namespace vortex;


use vortex;
use vortex\http\Request;
use vortex\http\Response;
use vortex\mvc\Dispatcher;
use vortex\routing\Route;
use vortex\routing\Router;
use vortex\utils\Logger;

class Application {
    const ROUTES_FILE_NAME = 'routes.cfg';

    private static $httpRequest;
    /**
     * @var Response
     */
    private static $httpResponse;
    /**
     * @var Router
     */
    private static $router;

    public static function init() {
        Application::registerClassLoader();
        Application::registerHandlers();

        Application::$httpRequest = new Request();
        Application::$httpResponse = new Response();

        Application::$router = new Router();
        $routesFile = new \SplFileObject(APP_PATH . DIRECTORY_SEPARATOR . self::ROUTES_FILE_NAME);
        Application::$router->parseRules($routesFile, true);
    }

    public static function dispatch() {
        /** @var $route Route */
        $route = Application::$router->route(Application::$httpRequest);
        if ($route === NULL)
            throw new vortex\routing\RouterException("No route defined for URL: " . Application::$httpRequest->getRawUrl());

        $dispatcher = new Dispatcher(Application::$httpRequest, Application::$httpResponse, $route);
        $dispatcher->dispatch();
    }

    public static function display() {
        Application::$httpResponse->send();
    }

    /**
     * Loads class-loader path
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