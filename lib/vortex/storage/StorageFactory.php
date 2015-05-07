<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\storage;


use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class CacheFactory builds a cache object
 */
abstract class StorageFactory {
    const STORAGE_DRIVER_INTERFACE = "vortex\\storage\\drivers\\StorageDriverInterface";
    const FILE_DRIVER = 'vortex\\storage\\drivers\\FileStorageDriver';
    const SESSION_DRIVER = 'vortex\\storage\\drivers\\SessionStorageDriver';

    public static $masterSwitch;

    /**
     * Constructs a cache object based on specific adapter and it's options
     * @param string $driver driver name (use const of this class)
     * @param array $options options
     * @return StorageInterface configured cache object
     * @throws StorageException if error occupied

     * @deprecated
     */
    public static function getFactory($driver, $options = array()) {
        self::build($driver, $options);
    }

    /**
     * Constructs a cache object based on specific adapter and it's options
     * @param string $driver driver name (use const of this class)
     * @param array $options options
     * @throws StorageException
     * @return StorageInterface configured cache object
     */
    public static function build($driver, $options = array()) {
        if (!class_exists($driver))
            throw new StorageException('Driver <' . $driver . '> dosnt\'t exist!');

        $interfaces = class_implements($driver);
        if (!isset($interfaces[self::STORAGE_DRIVER_INTERFACE]))
            throw new StorageException('Driver is not an instance of ' . self::STORAGE_DRIVER_INTERFACE . ' interface!');

        $options['masterSwitch'] = self::$masterSwitch;
        if (!$options['masterSwitch'])
            Logger::warning("Warning! Global cache switch: " . $options['masterSwitch'] . '! Nothing will be cached!');


        /** @var $cacheObject \vortex\storage\drivers\StorageDriverInterface */
        $cacheObject = new $driver();
        $cacheObject->config($options);
        $cacheObject->check();

        return $cacheObject;
    }
}

StorageFactory::$masterSwitch = Config::getInstance()->cache->enabled(true);