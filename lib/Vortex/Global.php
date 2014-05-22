<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 22:15
 */

class Vortex_Global {
    private $registry;
    private static $_instance = null;

    protected function __construct() {
        $this->registry = new Vortex_Registry();
    }

    protected function __clone() { }

    static private function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->registry;
    }

    static public function set($key, $value) {
        self::getInstance()->$key = $value;
    }

    static public function get($key) {
        return self::getInstance()->$key;
    }
} 