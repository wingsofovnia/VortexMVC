<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 11-Jun-14
 * Time: 22:22
 */

namespace Vortex\Cache;

use Vortex\Cache\Drivers\CacheBackend;
use Vortex\Exceptions\CacheException;
use Vortex\Logger;

abstract class CacheFactory {
    const FILE_DRIVER = 'FileBackend';

    /**
     * Constructs a cache object based on specific adapter and it's options
     * @param string $driver driver name (use const of this class)
     * @param array $options options
     * @return Cache configured cache object
     * @throws \Vortex\Exceptions\CacheException if error occupied
     */
    public static function getFactory($driver, $options = array()) {
        $driver = 'Vortex\Cache\Drivers\\' . $driver;

        if (!class_exists($driver))
            throw new CacheException('Driver <' . $driver . '> is not a class!');

        $interfaces = class_implements($driver);
        if (!isset($interfaces['Vortex\Cache\Drivers\CacheBackend']))
            throw new CacheException('Driver is not an instance of CacheBackend interface!');

        if (!isset($options['lifetime'])) {
            $options['lifetime'] = CacheBackend::DEFAULT_LIFE_TIME;
            Logger::warning('Lifetime was not specified. Using CacheBackend::DEFAULT_LIFE_TIME instead!');
        }

        if (!isset($options['namespace'])) {
            $options['namespace'] = CacheBackend::DEFAULT_NAMEPSACE;
            Logger::warning('Namespace was not specified. Using CacheBackend::DEFAULT_NAMEPSACE instead!');
        }

        $cacheObject = new $driver();
        $cacheObject->config($options);
        $cacheObject->check();

        return $cacheObject;
    }
} 