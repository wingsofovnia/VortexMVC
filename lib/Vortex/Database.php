<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

namespace Vortex\Database;
use Vortex\Config;
use Vortex\Database\DAO\Manager;
use Vortex\Logger;

/**
 * Class Vortex_Connection
 * This class establishes connection to database
 * using PDO and wraps it with FluentPDO
 * @link https://github.com/lichtner/fluentpdo
 */
class Connection {
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
        $configs = Config::getInstance();
        $this->driver = $configs->database->driver('mysql');
        $this->host = $configs->database->host('localhost');
        $this->user = $configs->database->user('root');
        $this->password = $configs->database->password('');
        $this->db = $configs->database->db('VortexMVC');

        $this->connect();
    }

    /**
     * Connects to database and making FluetPDO instance
     * @throws \PDOException if connection failed
     */
    private function connect() {
        $pdo = new \PDO($this->driver . ':host=' . $this->host . ';dbname=' . $this->db, $this->user, $this->password);

        require LIB_PATH . '/FluentPDO/FluentPDO.php';
        $this->connection = new \FluentPDO($pdo);

        if (!Config::isProduction())
            $this->connection->debug = function($BaseQuery) {
                Logger::debug("Query: " . $BaseQuery->getQuery() . "\nParameters: " . implode(', ', $BaseQuery->getParameters()) . "\n");
            };

        Logger::debug("Connected to database!");
    }

    protected function __clone() { }

    /**
     * Gets a FluentPDO connection
     * @return \FluentPDO instance
     */
    public static function getConnection() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance->connection;
    }
}