<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\cache\drivers;

use vortex\cache\ICache;

/**
 * Interface for cache backend
 */
interface ICacheBackend extends ICache {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMESPACE = 'vf';

    function check();

    function config($options);
}