<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 19:23
 */

namespace vortex\facade;

use vortex\auth\AuthManager;

class Auth {
    private static $manager;

    public static function setAuthManager(AuthManager $manager) {
        self::$manager = $manager;
    }

    public static function __callStatic($name, array $arguments) {
        return call_user_func_array(array(self::$manager, $name), $arguments);
    }
}