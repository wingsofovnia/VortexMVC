<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 28-May-14
 */

namespace vortex\http;
/**
 * Class Vortex_Session is a PHP Sessions wrapper with namespaces
 */
class Session {
    const GLOBAL_NAMESPACE = '-1';
    private static $_isStarted = false;

    private static $_instances = array();

    private $_namespace;
    private $autoDelete = false;

    private function __construct($namespace) {
        $this->setNamespace($namespace);
        self::$_instances[$namespace] = $this;
    }

    /**
     * Session factory
     * @param string $namespace session's namespace
     * @return Session a session object
     */
    public static function getSession($namespace = Session::GLOBAL_NAMESPACE) {
        if (isset(self::$_instances[$namespace]))
            return self::$_instances[$namespace];
        return new Session($namespace);
    }

    /**
     * Checks if session has been started already
     * @return bool if session is started, false - if not
     */
    private static function isStarted() {
        return self::$_isStarted;
    }

    /**
     * Starts a session
     * @throws SessionException if headers have been already sent
     */
    private static function start() {
        if (self::isStarted())
            return;
        if (headers_sent())
            throw new SessionException('Can\'t start session coz headers have been already started!');
        session_start();
        self::$_isStarted = true;
    }

    /**
     * Cleans a namespaced session
     * @return bool result OK-true, false - session even is not started
     * @throws SessionException
     */
    public function clean() {
        if (!self::isStarted())
            return false;

        if (!isset($_SESSION[$this->getNamespace()]))
            throw new SessionException("Session object is inconsistent!");

        $_SESSION[$this->getNamespace()] = array();
        return true;
    }

    /**
     * Writes key-value pair or array values into Session
     * @param string|array $key a key for key-value pair, or ASSOC array
     * @param mixed $value a value
     * @throws \InvalidArgumentException
     */
    public function set($key, $value = null) {
        if (empty($key))
            throw new \InvalidArgumentException('Param $key must be not empty value!');

        if (!self::isStarted())
            self::start();

        $session = &$_SESSION[$this->getNamespace()];

        if (is_array($key) && count(array_filter(array_keys($key), 'is_string')) == true) {
            foreach ($key as $k => $v)
                $session[$k] = $v;
        } else {
            $session[$key] = $value;
        }
    }

    /**
     * Picks the data from Session
     * @param string $key a key
     * @return mixed a value
     */
    public function get($key) {
        if (!self::isStarted())
            self::start();

        $session = &$_SESSION[$this->getNamespace()];

        if (isset($session[$key])) {
            $return = $session[$key];
            if ($this->autoDelete)
                unset($session[$key]);
            return $return;
        }

        return null;
    }

    /**
     * Magic alias of $this->get(k);
     * @param string $key a key
     * @return mixed a value
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Magic alias for $this->set(k, v);
     * @param string $key a key
     * @param mixed $value a value
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    /**
     * Gets a SESSION namespace name
     * @return string namespace name
     */
    public function getNamespace() {
        return $this->_namespace;
    }

    /**
     * Sets a namespace of a SESSION obj
     * @param string $namespace a session's namespace name
     * @throws \InvalidArgumentException if param $namespace is empty
     */
    private function setNamespace($namespace) {
        if (empty($namespace))
            throw new \InvalidArgumentException('Param $namespace should be not empty!');
        $this->_namespace = $namespace;
        $_SESSION[$namespace] = array();
    }

    /**
     * Enables auto deleting session values after reading
     */
    public function enableAutoDelete() {
        $this->autoDelete = true;
    }

    /**
     * Disables auto deleting session values after reading
     */
    public function disableAutoDelete() {
        $this->autoDelete = false;
    }

}