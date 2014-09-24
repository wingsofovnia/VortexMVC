<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace Vortex\Cache\Drivers;

use Vortex\Cache\Cache;

/**
 * Interface for cache backend
 * @package Vortex\Cache\Drivers
 */
interface CacheBackend extends Cache {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMEPSACE = 'vf';

    function check();

    function config($options);
}