<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:08
 */

namespace vortex\auth;


use vortex\database\ConnectionInterface;
use vortex\hashing\HasherInterface;
use vortex\mvc\model\PDOConnection;
use vortex\utils\Logger;

class DatabaseUserProvider implements UserProviderInterface {
    /**
     * The active database connection.
     *
     * @var PDOConnection
     */
    protected $conn;
    /**
     * The hasher implementation.
     *
     * @var HasherInterface
     */
    protected $hasher;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table;
    protected $idColumn;
    protected $passColumn;


    /**
     * Create a new database user provider.
     *
     * @param \vortex\database\ConnectionInterface $conn
     * @param \vortex\hashing\HasherInterface $hasher
     * @param  string $table
     * @param $idColumn
     * @param $passColumn
     * @return \vortex\auth\DatabaseUserProvider
     */
    public function __construct(ConnectionInterface $conn, HasherInterface $hasher, $table, $idColumn, $passColumn) {
        $this->conn = $conn;
        $this->table = $table;
        $this->hasher = $hasher;
        $this->idColumn = $idColumn;
        $this->passColumn = $passColumn;
    }


    public function retrieveById($identifier) {
        $assoc = $this->conn->first("SELECT * FROM " . $this->table . " WHERE " . $this->idColumn . " = :value",
                     array(':value' => $identifier));
        if (!$assoc)
            return false;
        return new GenericUser($assoc);
    }

    public function retrieveByCredentials(array $credentials) {
        $id = key($credentials);
        $pass = current($credentials);
        $assoc = $this->conn->first("SELECT * FROM " . $this->table . " WHERE " . $this->idColumn . " = :id AND " . $this->passColumn . " = :password",
                     array(':id'       => $id,
                           ':password' => $pass));
        if (!$assoc)
            return false;
        return new GenericUser($assoc);
    }

    public function validateCredentials(UserInterface $user, array $credentials) {
        $id = key($credentials);
        $pass = current($credentials);
        $assoc = $this->conn->first("SELECT * FROM " . $this->table . " WHERE " . $this->idColumn . " = :id AND " . $this->passColumn . " = :password",
                     array(':id'       => $id,
                           ':password' => $pass));
        if (!$assoc)
            return false;
        return new $user->getAuthIdentifier() == $assoc[$this->idColumn] && $user->getAuthPassword() == $assoc[$this->passColumn];
    }
}