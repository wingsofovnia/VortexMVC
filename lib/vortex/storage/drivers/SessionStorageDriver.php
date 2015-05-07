<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 19:42
 */

namespace vortex\storage\drivers;

use vortex\http\Session;

class SessionStorageDriver implements StorageDriverInterface {
    const DEFAULT_SESSION_NAMESPACE = '__storage';

    /**
     * @var Session
     */
    private $session;

    function check() {
        return !headers_sent();
    }

    function config($options) {
        $namespace = isset($options['namespace']) ? $options['namespace'] : self::DEFAULT_SESSION_NAMESPACE;
        $this->session = Session::getSession($namespace);
    }

    function save($id, $data = null, $time = null) {
        if (empty($data))
            return false;
        if (!is_string($data))
            $data = serialize($data);
        $this->session->set($id, $data);
        return true;
    }

    function load($id) {
        return $this->session->get($id);
    }

    function delete($id) {
        $this->session->remove($id);
    }

    function clean() {
        $this->session->clean();
    }
}