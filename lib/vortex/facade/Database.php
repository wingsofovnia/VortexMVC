<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 19:23
 */

namespace vortex\facade;


use vortex\database\ConnectionInterface;

class Database {
    private static $connection;

    public static function setConnection(ConnectionInterface $connection) {
        self::$connection = $connection;
    }

    public static function __callStatic($name, array $arguments) {
        return call_user_func_array(array(self::$connection, $name), $arguments);
    }
}