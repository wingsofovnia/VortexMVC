<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\application;
use vortex\mvc\controller\FrontController;
use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask @see Vortex_Router for path and
 * starts particular controller's action.
 * @deprecated should be moved to bootstrap class
 */
class Application {

    /**
     * Runs the application
     */
    public static function run() {
        Application::registerClassLoader();
        Application::registerHandlers();
        Application::initApplication();

        $front = new FrontController();
        $front->run();
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