<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex;
use vortex\utils\Logger;

/**
 * Class Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask Router class for path and
 * starts particular controller's action.
 */
class Application {
    const BOOTSTRAP_CLASS = 'application\Bootstrap';
    /**
     * Runs the application
     */
    public static function run() {
        Application::registerClassLoader();
        Application::registerHandlers();

        /* Running bootstrap */
        if (class_exists(Application::BOOTSTRAP_CLASS)) {
            $cls = Application::BOOTSTRAP_CLASS;
            $bootstrap = new $cls();
            $bootstrap->process();
        }
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