<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex;
use vortex\backbone\Backbone;
use vortex\backbone\vertebrae\FacadeVertebra;
use vortex\backbone\vertebrae\MVCVertebra;
use vortex\backbone\vertebrae\RouterVertebra;
use vortex\http\Request;
use vortex\http\Response;
use vortex\utils\Logger;

/**
 * Class Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask Router class for path and
 * starts particular controller's action.
 */
class Application {
    /**
     * Runs the application
     */
    public static function run() {
        Application::registerClassLoader();
        Application::registerHandlers();
        $httpRequest = new Request();
        $httpResponse = new Response();

        /* Running backbone */
        $backboneBootstrap = new Backbone($httpRequest, $httpResponse);

        /* Registered vertebrae */
        $backboneBootstrap->addVertebra(new FacadeVertebra());
        $backboneBootstrap->addVertebra(new RouterVertebra());
        $backboneBootstrap->addVertebra(new MVCVertebra());
        $backboneBootstrap->run();

        $httpResponse->send();
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