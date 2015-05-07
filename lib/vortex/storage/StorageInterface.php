<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\storage;

/**
 * Interface Cache
 */
interface StorageInterface {
    const UNLIMITED_LIFE_TIME = -1;

    function save($id, $data = null, $time = null);

    /**
     * Loads data from storage by id
     * @param string|int $id an identifier of record
     * @return mixed|null data or nul
     */
    function load($id);

    function delete($id);

    function clean();
} 