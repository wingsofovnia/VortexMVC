<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\cache;

use vortex\cache\drivers\CacheBackend;
use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class CacheFactory builds a cache object
 */
abstract class CacheFactory {
    const FILE_DRIVER = 'FileBackend';

    public static $masterSwitch;

    /**
     * Constructs a cache object based on specific adapter and it's options
     * @param string $driver driver name (use const of this class)
     * @param array $options options
     * @return Cache configured cache object
     * @throws CacheException if error occupied
     *
     * @deprecated
     */
    public static function getFactory($driver, $options = array()) {
        self::build($driver, $options);
    }

    /**
     * Constructs a cache object based on specific adapter and it's options
     * @param string $driver driver name (use const of this class)
     * @param array $options options
     * @throws CacheException
     * @return Cache configured cache object
     */
    public static function build($driver, $options = array()) {
        $driver = 'vortex\cache\drivers\\' . $driver;

        if (!class_exists($driver))
            throw new CacheException('Driver <' . $driver . '> is not a class!');

        $interfaces = class_implements($driver);
        if (!isset($interfaces['vortex\cache\drivers\CacheBackend']))
            throw new CacheException('Driver is not an instance of CacheBackend interface!');

        if (!isset($options['lifetime'])) {
            $options['lifetime'] = CacheBackend::DEFAULT_LIFE_TIME;
            Logger::warning('Lifetime was not specified. Using CacheBackend::DEFAULT_LIFE_TIME instead!');
        }

        if (!isset($options['namespace'])) {
            $options['namespace'] = CacheBackend::DEFAULT_NAMESPACE;
            Logger::warning('Namespace was not specified. Using CacheBackend::DEFAULT_NAMEPSACE instead!');
        }

        $options['masterSwitch'] = self::$masterSwitch;
        if (!$options['masterSwitch'])
            Logger::warning("Warning! Global cache switch: " . $options['masterSwitch'] . '! Nothing will be cached!');


        /** @var $cacheObject \vortex\cache\drivers\CacheBackend*/
        $cacheObject = new $driver();
        $cacheObject->config($options);
        $cacheObject->check();

        return $cacheObject;
    }
}

CacheFactory::$masterSwitch = Config::getInstance()->cache->enabled(true);