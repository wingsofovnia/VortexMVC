<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 28-May-14
 */

namespace Vortex;

use Vortex\Exceptions\SessionException;

/**
 * Class Vortex_Session is a PHP Sessions wrapper with namespaces
 * @package Vortex
 */
class Session {
    const GLOBAL_SCOPE = 0;
    const NAMESPACE_PREFIX = '__';

    private static $isStarted = false;
    private $namespace;

    private $autoDelete = false;

    /**
     * Constructs a namespaced session
     * @param string|int $namespace name of namespace (Vortex_Session::GLOBAL_SCOPE - global)
     */
    public function __construct($namespace = Session::GLOBAL_SCOPE) {
        $this->setNameSpace($namespace);
    }

    /**
     * Destroys all values from session, with current namespace
     * WARNING! If isGlobalNamespace() === true, than ALL values, from ALL namespaces will be destroyed!
     * @return bool false, if no values were set, otherwise - true;
     */
    public function destroy() {
        if ($this->isGlobalNamespace())
            self::destroyAll();

        $namespace = self::NAMESPACE_PREFIX . $this->namespace;

        if (!isset($_SESSION[$namespace]))
            return false;

        unset($_SESSION[$namespace]);
        return true;
    }

    /**
     * Checks if session namespace is global
     * @return bool true if global
     */
    public function isGlobalNamespace() {
        return $this->namespace == Session::GLOBAL_SCOPE;
    }

    /**
     * Destroys all sessions and it's data
     */
    public static function destroyAll() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        self::$isStarted = false;
    }

    /**
     * Gets a name of session's namepsace
     * @return string namespace
     */
    public function getNameSpace() {
        return $this->namespace;
    }

    /**
     * Change current object's namespace
     * @param string|int $namespace name of namespace (Vortex_Session::GLOBAL_SCOPE - global)
     * @throws \InvalidArgumentException if name is empty
     */
    public function setNameSpace($namespace) {
        if (empty($namespace) && $namespace != Session::GLOBAL_SCOPE)
            throw new \InvalidArgumentException('Namespace should be not empty string or Vortex_Session::GLOBAL_SCOPE!');
        $this->namespace = $namespace;
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
     * Writes key-value pair or array values into Session
     * @param string|array $key a key for key-value pair, or ASSOC array
     * @param mixed $value a value
     * @throws \InvalidArgumentException
     */
    public function set($key, $value = null) {
        if (empty($key))
            throw new \InvalidArgumentException('Param $key must be not empty value!');

        if (!self::isstarted())
            self::start();

        if (!$this->isGlobalNamespace()) {
            $namespace = self::NAMESPACE_PREFIX . $this->namespace;
            $session = $_SESSION[$namespace];
        } else {
            $session = $_SESSION;
        }

        if (is_array($key) && count(array_filter(array_keys($key), 'is_string')) == true) {
            foreach ($key as $k => $v)
                $session[$k] = $v;
        } else {
            $session[$key] = $value;
        }
    }

    /**
     * Checks if session has been started already
     * @return bool if session is started, false - if not
     */
    public static function isStarted() {
        return self::$isStarted;
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
        self::$isStarted = true;
    }

    /**
     * Picks the data from Session
     * @param string $key a key
     * @return mixed a value
     */
    public function get($key) {
        if (!$this->isGlobalNamespace()) {
            $namespace = self::NAMESPACE_PREFIX . $this->namespace;
            $session = & $_SESSION[$namespace];
        } else {
            $session = & $_SESSION;
        }

        if (isset($session[$key])) {
            $return = $session[$key];
            if ($this->autoDelete)
                unset($session[$key]);
            return $return;
        }

        return null;
    }
} 