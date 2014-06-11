<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 11-Jun-14
 * Time: 20:30
 */

namespace Vortex\Cache\Drivers;

interface CacheBackend {
    const DEFAULT_LIFE_TIME = 300;
    const DEFAULT_NAMEPSACE = 'vc';

    function check();
    function config($options);

    function save($id, $data = null, $time = null);
    function load($id);
    function delete($id);
    function clean();
}