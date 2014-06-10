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
    private $namespace;

    private $autoDelete = false;

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
        $_SESSION[$namespace] = array();
    }

    /**
     * Gets a name of session's namepsace
     * @return string namespace
     */
    public function getNameSpace() {
        return $this->namespace;
    }

    /**
     * Checks if session namespace is global
     * @return bool true if global
     */
    public function isGlobalNamespace() {
        return $this->namespace == Vortex_Session::GLOBAL_SCOPE;
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
     * Writes data into Session
     * @param string $key a key
     * @param mixed $value a value
     */
    public function __set($key, $value) {
        if (!self::isStarted())
            self::start();
        if ($this->isGlobalNamespace()) {
            $namespace = '__' . $this->namespace;
            $_SESSION[$namespace][$key] = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Picks the data from Session
     * @param string $key a key
     * @return mixed a value
     */
    public function __get($key) {
        if ($this->isGlobalNamespace()) {
            $session = &$_SESSION['__' . $this->namespace];
        } else {
            $session = &$_SESSION;
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