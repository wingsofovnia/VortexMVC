<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\cache\drivers;

use vortex\cache\Cache;

/**
 * Interface for cache backend
 */
interface CacheBackend extends Cache {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMESPACE = 'vf';

    function check();

    function config($options);
}