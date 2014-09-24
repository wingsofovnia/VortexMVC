<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace Vortex\Application;
use Vortex\MVC\Controller\FrontController;
use Vortex\Utils\Config;
use Vortex\Utils\Logger;

/**
 * Class Vortex_Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask @see Vortex_Router for path and
 * starts particular controller's action.
 * @package Vortex
 * @subpackage Application
 * @deprecated should be moved to bootstrap class
 */
class Application {

    /**
     * Runs the application
     */
    public static function run() {
        ClassLoader::register();
        Application::registerHandlers();
        Application::initApplication();

        $front = new FrontController();
        $front->run();
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