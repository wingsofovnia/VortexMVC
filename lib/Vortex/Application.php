<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace Vortex;

/**
 * Class Vortex_Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask @see Vortex_Router for path and
 * starts particular controller's action.
 * @package Vortex
 */
class Application {

    /**
     * Runs the application
     */
    public static function run() {
        Application::registerAutoLoader();
        Application::registerHandlers();
        Application::initApplication();

        $front = new FrontController();
        $front->run();
    }

    /**
     * Registers AutoLoader with `spl_autoload_register`
     */
    private static function registerAutoLoader() {
        require LIB_PATH . '/utils/SplClassLoader.php';

        $libLoader = new \SplClassLoader('Vortex', LIB_PATH);
        $libLoader->register();

        $appLoader = new \SplClassLoader('Application', ROOT_PATH);
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
            Logger::error($message . "\n" . $file . ' at line ' . $line);
        });
    }

    /**
     * Setups some other parts of application
     */
    private static function initApplication() {
        Logger::level(Config::getInstance()->logger->level(0));
    }
}