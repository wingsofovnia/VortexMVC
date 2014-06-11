<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 11-Jun-14
 * Time: 22:21
 */

namespace Vortex\Cache;

interface Cache {
    function save($id, $data = null, $time = null);
    function load($id);
    function delete($id);
    function clean();
} 