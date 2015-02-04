<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\cache\drivers;

use vortex\cache\Cache;
use vortex\cache\CacheException;
use vortex\utils\Logger;

/**
 * Class implements a File caching Adapter
 */
class FileBackend implements CacheBackend {
    private $namespace;
    private $defaultLifetime;
    private $cacheExtension;
    private $path;

    private $enabled;

    public function check() {
        $path = $this->getPath();
        if (!file_exists($path)) {
            if (!@mkdir($path, 0777)) {
                throw new CacheException("Please CHMOD " . $this->getPath() . " - 0777 or any writable permission!", 92);
            }
        } elseif (!is_writeable($path)) {
            @chmod($path, 0777);
        }
        return true;
    }

    public function config($options) {
        $this->path = isset($options['path']) ? $options['path'] : ROOT_PATH . '/temp';
        $this->cacheExtension = isset($options['extension']) ? $options['extension'] : '.cache';
        $this->defaultLifetime = $options['lifetime'];
        $this->namespace = $options['namespace'];
        $this->enabled = $options['masterSwitch'];
        Logger::info('FileBackend = {"path" => ' . $this->path . '", "cacheExtension" => ' . $this->cacheExtension .
            ', "defaultLifetime" => ' . $this->defaultLifetime . ', "namespace" => ' . $this->namespace);
    }

    public function save($id, $data = null, $time = null) {
        if (!$this->enabled)
            return false;
        if (!$time)
            $time = $this->defaultLifetime;
        $path = $this->getPath($id);
        $data = array(
            'lifetime' => $time,
            'data' => $data
        );
        Logger::debug('Writing to ' . $path);
        $res = file_put_contents($path, serialize($data));
        Logger::debug('Saved data!');
        Logger::debug($res);
        return $res !== false;
    }

    public function load($id) {
        if (!$this->enabled)
            return null;
        $path = $this->getPath($id);
        Logger::debug('Check ' . $path);
        if (!file_exists($path))
            return false;

        $data = file_get_contents($path);
        $data = unserialize($data);

        if (array_keys($data) != array('lifetime', 'data')) {
            Logger::warning('Cache file id = #' . $id . ' bad and was deleted!');
            $this->delete($id);
            return false;
        }

        if (time() - filemtime($path) > $data['lifetime'] && $data['lifetime'] != Cache::UNLIMITED_LIFE_TIME) {
            Logger::debug('Cache file id = #' . $id . ' old and was deleted!');
            $this->delete($id);
            return false;
        }
        Logger::debug('Cache file ' . $path . ' loaded successful1');
        return $data['data'];
    }

    public function delete($id) {
        $file = $this->getPath($id);
        $wasDeleted = @unlink($file);
        if (!$wasDeleted)
            Logger::error('Failed to delete cache file ' . $file . '!');
        return $wasDeleted;
    }

    public function clean() {
        $files = glob($this->getPath() . '/*');
        $cleaned = true;
        foreach ($files as $file) {
            if (is_file($file)) {
                $wasDeleted = @unlink($file);
                if (!$wasDeleted) {
                    $cleaned = false;
                    Logger::error('Failed to delete cache file ' . $file . '!');
                }
            }
        }
        return $cleaned;
    }

    private function getPath($id = null) {
        if (!$id)
            return $this->path;
        return $this->path . '/' . $this->namespace . '_' . $id . $this->cacheExtension;
    }
}