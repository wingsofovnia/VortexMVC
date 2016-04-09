<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\utils;

use ArrayObject;
use IniParser;
use vortex\storage\StorageFactory;
use vortex\storage\StorageInterface;

require 'IniParser.php';

/**
 * Class Config parses application configurations file application.ini and represents
 * settings in a object-oriented manner.
 *
 * Supports section inheritance, property nesting, simple arrays and optional default values:
 * application.ini:
 * mysection.my_prop=12
 *
 * Code:
 * Config::getInstance()->mysection->my_prop('default-value'); // -> default-value
 * Config::getInstance()->mysection->my_prop('default-value'); // -> 12
 *
 * @package vortex\utils
 */
class Config extends IniParser {
    const APPLICATION_SETTINGS_FILE = '/application.ini';
    private static $_instance;

    private $config;
    private static $production;

    /**
     * Singleton initializer
     * @return object settings
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self(APP_PATH . Config::APPLICATION_SETTINGS_FILE);
        }
        return self::$_instance->config;
    }

    /**
     * Parses settings file and caching it
     * @param string $file path to file
     * @throws \RuntimeException if config file 'environment' doesn't exist or points on non-existent section
     */
    public function __construct($file = null) {
        parent::__construct($file);
        $this->config = $this->parse();

        /* Determining environment */
        $stage = $this->config->environment('production');
        if (!is_object($this->config->$stage))
            throw new \RuntimeException('Settings file doesn\'t have <' . $stage . '> stage!');
        self::$production = $stage == 'production';
        $this->config = $this->config->$stage;
    }

    /**
     * Overloading for using our ArrayObject with magic caller
     * @param array $array
     * @return array|\ArrayObject|ArrayObjectMagic
     */
    protected function getArrayValue($array = array()) {
        if ($this->use_array_object) {
            return new ArrayObjectMagic($array);
        } else {
            return $array;
        }
    }

    /**
     * Checks if environment is production
     * @return bool if environment is production
     */
    public static function isProduction() {
        return self::$production;
    }
}

/**
 * Class ArrayObjectMagic
 * This class is the same as it's parent, except magic method,
 * that allows to set default value for value.
 *
 * Example:
 * $o->myvar -> error, if myvar doesn't exist (was before)
 * $o->myvar('default") -> myvar or 'default', if myvar doesn't exist (now)
 */
class ArrayObjectMagic extends ArrayObject {
    public function __call($method, $args) {
        if (isset($this[$method]))
            return $this[$method];
        if (isset($args[0]))
            return $args[0];
        return new ArrayObjectMagic();
    }

    public function __get($name) {
        if (isset($this[$name]))
            return $this[$name];
        $magic = new ArrayObjectMagic();
        return $magic;
    }
}