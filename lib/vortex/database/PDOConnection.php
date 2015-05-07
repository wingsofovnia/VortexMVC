<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\database;
use vortex\utils\Logger;

class PDOConnection implements ConnectionInterface {
    private $driver;
    private $host;
    private $user;
    private $password;
    private $db;

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct($driver = 'mysql', $host = 'localhost', $user = 'root', $password = '', $db = 'VortexMVC') {
        $this->driver = $driver;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->db = $db;

        $this->connect();
    }

    /**
     * Connects to database and making FluetPDO instance
     * @throws \PDOException if connection failed
     */
    protected function connect() {
        $this->connection = new \PDO($this->driver . ':host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->password);
        Logger::debug("Connection is");
    }

    protected function __clone() {
    }

    public function select($query, $bindings = array()) {
        $stmt = $this->prepare($query, $bindings);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function first($query, $bindings = array()) {
        $stmt = $this->prepare($query, $bindings);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function insert($query, $bindings = array()) {
        return $this->prepare($query, $bindings)->execute();
    }

    public function update($query, $bindings = array()) {
        return $this->prepare($query, $bindings)->execute();
    }

    public function delete($query, $bindings = array()) {
        return $this->prepare($query, $bindings)->execute();
    }

    private function prepare($query, $bindings = array()) {
        $stmt = $this->connection->prepare($query);
        foreach ($bindings as $key => &$value)
            $stmt->bindParam($key, $value);
        return $stmt;
    }

    public function exec($query) {
        return $this->connection->exec($query);
    }

    public function beginTransaction() {
        $this->connection->beginTransaction();
        return $this;
    }

    public function commit() {
        $this->connection->commit();
        return $this;
    }

    public function rollBack() {
        $this->connection->rollBack();
        return $this;
    }
}