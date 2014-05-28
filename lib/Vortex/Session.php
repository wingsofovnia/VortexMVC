<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 28-May-14
 */

/**
 * Class Vortex_Session
 * A PHP Sessions wrapper with namespaces
 */
class Vortex_Session {
    const GLOBAL_SCOPE = 0;

    private static $isStarted = false;
    public $isGlobalNamespace;
    public $namespace;

    /**
     * Starts a session
     * @throws Vortex_Exception_SessionError if headers have been already sent
     */
    public static function start() {
        if (self::isStarted())
            return;
        if (headers_sent())
            throw new Vortex_Exception_SessionError('Can\'t start session coz headers have been already started!');
        session_start();
        self::$isStarted = true;
    }

    /**
     * Destroys session and it's data
     */
    public static function destroy() {
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
     * Checks if session has been started already
     * @return bool if session is started, false - if not
     */
    public static function isStarted() {
        return self::$isStarted;
    }

    /**
     * Constructs a namespaced session
     * @param string|int $namespace name of namespace (Vortex_Session::GLOBAL_SCOPE - global)
     */
    public function __construct($namespace = Vortex_Session::GLOBAL_SCOPE) {
        $this->setNameSpace($namespace);
    }

    /**
     * Change current object's namespace
     * @param string|int $namespace name of namespace (Vortex_Session::GLOBAL_SCOPE - global)
     * @throws Vortex_Exception_IllegalArgument if name is empty
     */
    public function setNameSpace($namespace) {
        if (empty($namespace) && $namespace != Vortex_Session::GLOBAL_SCOPE)
            throw new Vortex_Exception_IllegalArgument('Namespace should be not empty string or Vortex_Session::GLOBAL_SCOPE!');
        $this->namespace = $namespace;
        $this->isGlobalNamespace = $namespace === Vortex_Session::GLOBAL_SCOPE ? true : false;
    }

    /**
     * Writes data into Session
     * @param string $key a key
     * @param mixed $value a value
     */
    public function __set($key, $value) {
        if (!self::isStarted())
            self::start();
        if ($this->isGlobalNamespace) {
            $_SESSION[$key] = $value;
        } else {
            $namespace = '__' . $this->namespace;
            $_SESSION[$namespace][$key] = $value;
        }
    }

    /**
     * Picks the data from Session
     * @param string $key a key
     * @param mixed $default a value, that will be returned, if there is no record in Session
     * @return mixed a value
     */
    public function __get($key, $default = null) {
        if ($this->isGlobalNamespace) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
        } else {
            $namespace = '__' . $this->namespace;
            return isset($_SESSION[$namespace][$key]) ? $_SESSION[$namespace][$key] : $default;
        }
    }
} 