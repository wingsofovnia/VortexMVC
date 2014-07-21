<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 29-May-14
 */

namespace Vortex;

/**
 * Class Auth used to authenticate user and determine its permissions
 * @package Vortex
 */
class Auth {
    const GUEST_LEVEL = -1;
    const ADMIN_LEVEL = 0;
    const SESSION_NAMESPACE = 'vf_auth';

    private static $table = 'vf_auth';
    private static $primaryKey = 'id';
    private static $identityColumn = 'username';
    private static $credentialColumn = 'password';
    private static $permissionLevelColumn = 'level';

    private static $logged = false;

    /**
     * Hash algorithm function
     * @var callable
     */
    private static $hashAlgorithm;

    /**
     * Connection to database
     * @var \FluentPDO
     */
    private static $connection;

    /**
     * Session variable
     * @var Session
     */
    private static $session;

    /**
     * Static initializer
     */
    public static function init() {
        self::$connection = Database::getConnection();
        self::setHashAlgorithm(function ($credential) {
            return md5($credential);
        });
        self::$session = new Session(self::SESSION_NAMESPACE);
    }

    /**
     * Authenticates user
     * @param string $identity username
     * @param string $credential user's password
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function login($identity, $credential) {
        if (empty($identity))
            throw new \InvalidArgumentException('$identity cant be empty string!');

        $user = self::identify($identity);
        Logger::debug($user);

        if (!$user || $user[self::$credentialColumn] != self::hashify($credential))
            return false;

        self::$session->set($user);
        self::$logged = true;
        return true;
    }

    /**
     * Returns user data by it's identity
     * @param string $identity username
     * @return array user data
     * @throws \InvalidArgumentException
     */
    public static function identify($identity) {
        if (empty($identity))
            throw new \InvalidArgumentException('$identity cant be empty string!');

        $userData = self::$connection->from(self::$table)
            ->where(self::$identityColumn, $identity)
            ->fetch();

        return $userData;
    }

    /**
     * Logouts user
     */
    public static function logout() {
        self::$session->destroy();
        self::$logged = false;
    }

    /**
     * Registers a new user
     * @param string $identity username
     * @param string $credential password
     * @param int $level permission level
     * @param array|null $additions addition user data
     * @return bool|int user's primary key, or false
     */
    public static function register($identity, $credential, $level, $additions = null) {
        if (self::identify($identity))
            return false;

        $user_data = array(
            self::$identityColumn => $identity,
            self::$credentialColumn => self::hashify((string)$credential),
            self::$permissionLevelColumn => (int)$level
        );

        if (is_array($additions))
            $user_data = array_merge($user_data, $additions);

        return self::$connection->insertInto(self::$table, $user_data)->execute();
    }

    /**
     * Updates user's data
     * @param string $identity username
     * @param array $data new user data
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function update($identity, $data) {
        if (empty($id))
            throw new \InvalidArgumentException('Param $id must be not empty!');
        else if (!is_array($data) || count($data) == 0)
            throw new \InvalidArgumentException('Param $data must be an array with length > 0');

        return (bool)self::$connection->update(self::$table,
            array_merge(
                array(self::$identityColumn => $identity),
                $data
            ))->execute();
    }

    /**
     * Deletes user by it's identity
     * @param string $identity username
     * @return bool
     */
    public static function deleteUser($identity) {
        return (bool)self::$connection->deleteFrom(self::$table)
            ->where(self::$identityColumn, $identity)
            ->execute();
    }

    /**
     * Checks if user is logged it
     * @return bool
     */
    public static function isLogged() {
        return self::$logged;
    }

    /**
     * Checks if user's level is {@see self::ADMIN_LEVEL}
     * @return bool
     */
    public static function isAdmin() {
        if (!self::isLogged())
            return false;

        return self::$session->get(self::$permissionLevelColumn) == self::ADMIN_LEVEL;
    }

    /**
     * Returns user's permission level
     * @return int level
     */
    public static function getUserLevel() {
        if (!self::isLogged())
            return self::GUEST_LEVEL;
        return (int)self::$session->get(self::$permissionLevelColumn);
    }

    /**
     * Gets a name of permission level column
     * @return string
     */
    public static function getPermissionLevelColumn() {
        return self::$permissionLevelColumn;
    }

    /**
     * Sets a name of permission level column
     * @param string $permissionLevelGroup
     * @throws \InvalidArgumentException if param $permissionLevelGroup is empty
     */
    public static function setPermissionLevelColumn($permissionLevelGroup) {
        if (empty($permissionLevelGroup))
            throw new \InvalidArgumentException('Param $permissionLevelGroup should be not empty!');
        self::$permissionLevelColumn = $permissionLevelGroup;
    }

    /**
     * Gets an user's credential column name
     * @return string
     */
    public static function getCredentialColumn() {
        return self::$credentialColumn;
    }

    /**
     * Sets an user's credential column name
     * @param string $credentialColumn
     * @throws \InvalidArgumentException if param $credentialColumn is empty
     */
    public static function setCredentialColumn($credentialColumn) {
        if (empty($credentialColumn))
            throw new \InvalidArgumentException('Param $credentialColumn should be not empty!');
        self::$credentialColumn = $credentialColumn;
    }

    /**
     * Gets an user's identity column name
     * @return string
     */
    public static function getIdentityColumn() {
        return self::$identityColumn;
    }

    /**
     * Sets an user's identity column name
     * @param string $identityColumn
     * @throws \InvalidArgumentException if param $identityColumn is empty
     */
    public static function setIdentityColumn($identityColumn) {
        if (empty($identityColumn))
            throw new \InvalidArgumentException('Param $identityColumn should be not empty!');
        self::$identityColumn = $identityColumn;
    }

    /**
     * Gets primary column name in auth table
     * @return string
     */
    public static function getPrimaryKey() {
        return self::$primaryKey;
    }

    /**
     * Sets primary column name in auth table
     * @param string $primaryKey
     * @throws \InvalidArgumentException if param $primaryKey is empty
     */
    public static function setPrimaryKey($primaryKey) {
        if (empty($primaryKey))
            throw new \InvalidArgumentException('Param $primaryKey should be not empty!');
        self::$primaryKey = $primaryKey;
    }

    /**
     * Gets a name of auth table
     * @return string
     */
    public static function getTable() {
        return self::$table;
    }

    /**
     * Sets a name of auth table
     * @param string $table
     * @throws \InvalidArgumentException if param $table is empty
     */
    public static function setTable($table) {
        if (empty($table))
            throw new \InvalidArgumentException('Param $table should be not empty!');
        self::$table = $table;
    }

    /**
     * Sets a function, for hashing password.
     * Example: function($pass) {return md5($pass);} (default algorithm)
     * @param callable $hashAlgorithm
     * @throws \InvalidArgumentException if param $hashAlgorithm is empty
     */
    public static function setHashAlgorithm($hashAlgorithm) {
        if (!is_callable($hashAlgorithm))
            throw new \InvalidArgumentException('Param $hashAlgorithm should be callable!');
        else if (func_num_args($hashAlgorithm) != 1)
            throw new \InvalidArgumentException('Param-callable $hashAlgorithm should has 1 argument!');
        self::$hashAlgorithm = $hashAlgorithm;
    }

    /**
     * Returns a hash of a credential processed with {@see $hashAlgorithm}
     * @param string $credential
     * @return string hash
     * @throws \InvalidArgumentException
     */
    public static function hashify($credential) {
        if (empty($credential))
            throw new \InvalidArgumentException('Param $credential should be not empty!');

        /** @var $hashAlgorithm callable */
        return self::$hashAlgorithm($credential);
    }
}

Auth::init();
