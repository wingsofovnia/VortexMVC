<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 24.09.2014
 */

namespace Vortex\Application;

class ClassLoader {
    public static function register() {
        require LIB_PATH . '/utils/SplClassLoader.php';

        $libLoader = new \SplClassLoader('Vortex', LIB_PATH);
        $libLoader->register();

        $appLoader = new \SplClassLoader('Application', ROOT_PATH);
        $appLoader->register();
    }
} 