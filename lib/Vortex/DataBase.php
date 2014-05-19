<?php

/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 23:48
 */
class Vortex_DataBase {
    private static $_instance = null;

    private $driver;
    private $host;
    private $user;
    private $password;
    private $db;
    private $connection;

    protected function __construct() {
        $configs = Vortex_Config::getInstance();
        $this->driver = $configs->getDbPDODriver();
        $this->host = $configs->getDbHost();
        $this->user = $configs->getDbUserName();
        $this->password = $configs->getDbPassword();
        $this->db = $configs->getDbDataBase();

        $this->connect();
    }

    private function connect() {
        try {
            $pdo = new PDO($this->driver . ':host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->password);
            include "lib/FluentPDO/FluentPDO.php";
            $this->connection = new FluentPDO($pdo);
            Vortex_Logger::debug("Connected to database!");
        } catch (PDOException $e) {
            throw new Vortex_Exception_DBError("Can't connect to DB!", 0, $e);
        }
    }

    protected function __clone() { }

    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->connection;
    }
} 