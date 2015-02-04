<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\cache;

/**
 * Interface Cache
 */
interface Cache {
    const UNLIMITED_LIFE_TIME = -1;

    function save($id, $data = null, $time = null);

    function load($id);

    function delete($id);

    function clean();
} 