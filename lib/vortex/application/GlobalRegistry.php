<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\application;

/**
 * Class Global
 * An implementation of Registry as a singleton in a global scope
 */
class GlobalRegistry {
    private $registry;
    private static $_instance = null;

    /**
     * Creates internal Registry object
     */
    protected function __construct() {
        $this->registry = new \ArrayObject();
    }

    protected function __clone() {
    }

    /**
     * Instance getter
     * @return \ArrayObject an object of internal registry
     */
    private static function getInstance() {
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
    public static function set($key, $value) {
        self::getInstance()->$key = $value;
    }

    /**
     * Gets a key-value pair from global registry
     * @param string $key a key of value
     * @return mixed value
     */
    public static function get($key) {
        return self::getInstance()->$key;
    }
} 