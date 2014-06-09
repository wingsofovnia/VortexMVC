<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Application
 * This one is an engine and class loader and configurator.
 * Class controls an output, ask @see Vortex_Router for path and
 * starts particular controller's action.
 */
class Vortex_Application {
    
    /**
     * Init constructor
     */
    public function __construct() {
        $this->registerAutoLoader();
        $this->registerHandlers();
		$this->fc = new Vortex_FrontController();
		$this->fc->run();
    }

    /**
     * Registers AutoLoader with `spl_autoload_register`
     */
    private function registerAutoLoader() {
        spl_autoload_register(function ($classname) {
            /* Is it a lib class? */
            $libs = glob(LIB_PATH . '/*' , GLOB_ONLYDIR);
            $libs = array_map(function($path) {return basename($path);}, $libs);

            $regexp = '/^[';
            foreach ($libs as $lib)
                $regexp .= $lib . '|';
            $regexp = rtrim($regexp, '|') . ']/';
            $isLib = preg_match($regexp, $classname) > 0;

            if ($isLib) {
                $path = LIB_PATH . '/' . str_replace('_', DIRECTORY_SEPARATOR, $classname);
            } else {
                $appClass = $classname . 's';
                $appClass = preg_split('/(?=[A-Z])/', $appClass);
                $appClass = array_values(array_filter($appClass));

                $dir = end($appClass);
                $path = APPLICATION_PATH . '/' . $dir . '/' . $classname;
            }
            require_once($path . '.php');
        });
    }

    private function registerHandlers() {
        set_exception_handler(function($e) {
           Vortex_Logger::exception($e->__toString());
        });
        set_error_handler(function($code, $message, $file, $line) {
            Vortex_Logger::error($message . "\n" . $file . ' at line ' . $line);
        });
    }
}

