<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 *
 * @package Vortex
 */

namespace Vortex;
use ArrayObject;
use IniParser;
use Vortex\Cache\Cache;
use Vortex\Cache\CacheFactory;

require LIB_PATH . '/Utils/IniParser.php';

/**
 * Class Vortex_Config
 * This class parses application ini settings file into OM
 */
class Config extends IniParser {
    const CACHE_NAMESPACE_TAG = 'config';
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
        $cache = CacheFactory::getFactory(CacheFactory::FILE_DRIVER, array('namespace' => 'vConfig'));
        $configCacheId = md5($file);
        $this->config = $cache->load($configCacheId);
        if (!$this->config) {
            $this->config = $this->parse();
            $cache->save($configCacheId, $this->config);
        }

        /* Determining environment */
        $stage = $this->config->environment('production');
        if (!isset($this->config->$stage))
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
            return new ArrayObjectMagic($array, ArrayObjectMagic::ARRAY_AS_PROPS);
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
        if (isset($this->$method))
            return $this->$method;
        return isset($args[0]) ? $args[0] : new ArrayObjectMagic();
    }
    public function __get($name) {
        return new ArrayObjectMagic();
    }
}