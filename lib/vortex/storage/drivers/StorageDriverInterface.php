<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\storage\drivers;

use vortex\storage\StorageInterface;

/**
 * Interface for cache backend
 */
interface StorageDriverInterface extends StorageInterface {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMESPACE = 'vf';

    function check();

    function config($options);
}