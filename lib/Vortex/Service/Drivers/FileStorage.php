<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 07-Jun-14
 * Time: 15:35
 */

class Vortex_Service_Drivers_FileStorage implements Vortex_Service_Drivers_IStorageDriver {
    public function load($id) {
        $data = file_get_contents($id);
        return unserialize($data);
    }

    public function save($id, $data) {
        $res = file_put_contents($id, serialize($data));
        return $res !== false;
    }

    public function delete($id) {
        unlink($id);
    }

    public function clean() {
        $path = $this->getFilePath();
        $files = glob($path . '/*.cache');

        foreach ($files as $file)
            unlink($file);
    }

    public function getMetaData($id) {
        if (!is_file($id))
            return null;
        return array('lifetime' => time() - filectime($id));
    }
}