<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 07-Jun-14
 * Time: 15:36
 */

interface Vortex_Service_Drivers_IStorageDriver {
    public function load($id);
    public function save($id, $data);
    public function delete($id);
    public function clean();
    public function getMetaData($id);
}