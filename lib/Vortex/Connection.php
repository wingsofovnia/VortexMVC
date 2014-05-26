<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Connection
 * This class makes connection to database
 * using PDO and that wraps it into FluentPDO
 * @link https://github.com/lichtner/fluentpdo
 */
class Vortex_Connection {
    private static $_instance = null;

    private $driver;
    private $host;
    private $user;
    private $password;
    private $db;
    private $connection;

    /**
     * Init constructor. Reads values from Vortex_Config
     * and making attempt to connect
     */
    protected function __construct() {
        $configs = Vortex_Config::getInstance();
        $this->driver = $configs->getDbPDODriver();
        $this->host = $configs->getDbHost();
        $this->user = $configs->getDbUserName();
        $this->password = $configs->getDbPassword();
        $this->db = $configs->getDbDataBase();

        $this->connect();
    }

    /**
     * Connects to database and making FluetPDO instance
     * @throws Vortex_Exception_DBError if connection failed
     */
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

    /**
     * Singleton instance getter
     * @return FluentPDO instance
     */
    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->connection;
    }
} 