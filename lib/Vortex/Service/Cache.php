<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 07-Jun-14
 * Time: 15:29
 */

class Vortex_Service_Cache {
    const UNLIMITED_LIFETIME = -1;

    protected $driver;

    protected $cache;
    protected $path;
    protected $namespace;

    protected $lifeTime;

    public function __construct(Vortex_Service_Drivers_IStorageDriver $driver) {
        $this->driver = $driver;
        $this->path = '/temp';
        $this->cache = true;
    }

    public function enableCache() {
        $this->cache = true;
    }

    public function disableCache() {
        $this->cache = false;
    }

    public function load($id) {
        $id = $this->getFilePath($id);
        $meta = $this->driver->getMetaData($id);
        if (!$meta)
            return false;
        if ($meta['lifetime'] >= $this->getLifeTime() && $this->getLifeTime() != self::UNLIMITED_LIFETIME) {
            $this->driver->delete($id);
            return false;
        }
        return $this->driver->load($id);
    }

    public function save($id, $data) {
        if ($this->cache !== true)
            return false;
        $this->driver->save($this->getFilePath($id), $data);
    }

    public function delete($id) {
        $this->driver->delete($this->getFilePath($id));
    }

    public function getLifeTime() {
        return $this->lifeTime;
    }

    public function setLifeTime($lifeTime) {
        $this->lifeTime = $lifeTime;
    }

    public function setPath($path) {
        if (!file_exists($path))
            throw new Vortex_Exception_IllegalArgument('Such path doesn\'t exist!');
        $this->path = $path;
    }

    public function setNamespace($ns) {
        if (empty($ns))
            throw new Vortex_Exception_IllegalArgument('Namespace cannot be empty!');
        $this->namespace = $ns;
    }

    protected function getFilePath($id = null) {
        if (!$this->path)
            throw new Vortex_Exception_IllegalArgument('Set path first!');
        $file = ROOT_PATH . $this->path;
        if ($this->namespace)
            $file .= '/' . $this->namespace;
        if ($id)
            $file .= '_' . md5($id) . '.cache';
        return $file;
    }
}