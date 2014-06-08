<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Connection
 * This class establishes connection to database
 * using PDO and wraps it with FluentPDO
 * @link https://github.com/lichtner/fluentpdo
 */
class Vortex_Database_Connection {
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
     * @throws PDOException if connection failed
     */
    private function connect() {
        $pdo = new PDO($this->driver . ':host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->password);
        $this->connection = new Vortex_Database_PDO($pdo);
        if (!Vortex_Config::getInstance()->isProduction())
            $this->connection->debug = function($BaseQuery) {
                Vortex_Logger::debug("Query: " . $BaseQuery->getQuery() . "\nParameters: " . implode(', ', $BaseQuery->getParameters()) . "\n");
            };
        Vortex_Logger::debug("Connected to database!");
    }

    protected function __clone() { }

    /**
     * Gets a FluentPDO connection
     * @return FluentPDO instance
     */
    public static function getConnection() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->connection;
    }
} 