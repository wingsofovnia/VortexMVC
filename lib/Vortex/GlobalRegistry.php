<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex;

/**
 * Class Global
 * An implementation of Registry as a singleton in a global scope
 * @package Vortex
 */
class GlobalRegistry {
    private $registry;
    private static $_instance = null;

    /**
     * Creates internal Registry object
     */
    protected function __construct() {
        $this->registry = new Registry();
    }

    protected function __clone() {
    }

    /**
     * Instance getter
     * @return Registry an object of internal registry
     */
    static private function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->registry;
    }

    /**
     * Adds a key-value pair into global registry
     * @param string $key a key
     * @param mixed $value a value
     */
    static public function set($key, $value) {
        self::getInstance()->$key = $value;
    }

    /**
     * Gets a key-value pair from global registry
     * @param string $key a key of value
     * @return mixed value
     */
    static public function get($key) {
        return self::getInstance()->$key;
    }
} 