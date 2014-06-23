<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 11-Jun-14
 * Time: 20:30
 */

namespace Vortex\Cache\Drivers;
use Vortex\Cache\Cache;

interface CacheBackend extends Cache {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMEPSACE = 'vf';

    function check();
    function config($options);
}