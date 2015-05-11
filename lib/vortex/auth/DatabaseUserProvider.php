<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:08
 */

namespace vortex\auth;


use vortex\database\ConnectionInterface;
use vortex\database\PDOConnection;
use vortex\hashing\HasherInterface;

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
     * @param  string $table
     * @param $idColumn
     * @param $passColumn
     * @internal param \vortex\hashing\HasherInterface $hasher
     * @return \vortex\auth\DatabaseUserProvider
     */
    public function __construct(ConnectionInterface $conn, $table, $idColumn, $passColumn) {
        $this->conn = $conn;
        $this->table = $table;
        $this->idColumn = $idColumn;
        $this->passColumn = $passColumn;
    }


    public function retrieveBy(array $options) {
        if (empty($options))
            throw new \InvalidArgumentException("empty options");

        $query = "SELECT * FROM " . $this->table . " WHERE ";
        $params = array();
        foreach ($options as $k => $v) {
            $param = ':' . $k;
            $params[] = array($param, $v);

            $query .= $k . ' = ' . $param;
            $query .= ' AND ';
        }
        $query = rtrim($query, ' AND ');

        $assoc = $this->conn->first($query, $params);

        if (!$assoc)
            return false;
        return new GenericUser($assoc);
    }

    public function retrieveByCredentials($identity, $password) {
        $assoc = $this->conn->first("SELECT * FROM " . $this->table . " WHERE " . $this->idColumn . " = :id AND " . $this->passColumn . " = :password",
                     array(':id'       => $identity,
                           ':password' => $password));
        if (!$assoc)
            return false;
        return new GenericUser($assoc);
    }

}