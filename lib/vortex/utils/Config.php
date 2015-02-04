<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\utils;

use ArrayObject;
use IniParser;
use vortex\cache\ACacheFactory;
use vortex\cache\ICache;

require 'IniParser.php';

/**
 * Class Vortex_Config
 * This class parses application ini settings file into OM
 */
class Config extends IniParser {
    const CACHE_NAMESPACE_TAG = 'config';
    const CACHE_TAG = 'vf_config';
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
            self::$_instance = new self(APPLICATION_PATH . Config::APPLICATION_SETTINGS_FILE);
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

        /* Caching configs... */
        $cache = ACacheFactory::build(ACacheFactory::FILE_DRIVER, array(
            'namespace' => Config::CACHE_TAG,
            'lifetime' => ICache::UNLIMITED_LIFE_TIME
        ));

        $configCacheId = md5($file);
        $this->config = $cache->load($configCacheId);
        if (!$this->config) {
            $this->config = $this->parse();
            $cache->save($configCacheId, $this->config);
        }

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