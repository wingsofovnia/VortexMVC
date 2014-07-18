<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 11-Jun-14
 */

namespace Vortex\Cache;

/**
 * Interface Cache
 * @package Vortex\Cache
 */
interface Cache {
    const UNLIMITED_LIFE_TIME = -1;

    function save($id, $data = null, $time = null);

    function load($id);

    function delete($id);

    function clean();
} 