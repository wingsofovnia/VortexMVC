<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 22:15
 */

class Vortex_Config {
    private static $_instance = null;

    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'pro';

    /* Configs */
    private $state;
    private $defaultCnA;
    private $errorCnA;
    private $viewExtension;
    private $isLayouts;
    private $layouts;
    private $defaultLayout;

    private $routes;

    private $dbPDODriver;
    private $dbHost;
    private $dbUserName;
    private $dbPassword;
    private $dbDataBase;

    protected function __construct() {
        $this->setState(Vortex_Config::DEVELOPMENT);
        $this->setDefaultController('index');
        $this->setDefaultAction('index');

        $this->setErrorController('error');
        $this->setErrorAction('index');

        $this->setViewExtension('tpl');
        $this->layouts = array();
        $this->enableLayouts(false);
    }

    protected function __clone() { }

    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getDbPDODriver() {
        return $this->dbPDODriver;
    }

    public function setDbPDODriver($dbPDODriver) {
        $availableDrivers = PDO::getAvailableDrivers();
        if (!in_array($dbPDODriver, $availableDrivers))
            throw new Vortex_Exception_DBError("No <$dbPDODriver> found!");
        $this->dbPDODriver = $dbPDODriver;
    }

    public function getDbHost() {
        return $this->dbHost;
    }

    public function setDbHost($dbHost) {
        $this->dbHost = $dbHost;
    }

    public function getDbDataBase() {
        return $this->dbDataBase;
    }

    public function setDbDataBase($dbDataBase) {
        $this->dbDataBase = $dbDataBase;
    }

    public function getDbPassword() {
        return $this->dbPassword;
    }

    public function setDbPassword($dbPassword) {
        $this->dbPassword = $dbPassword;
    }

    public function getDbUserName() {
        return $this->dbUserName;
    }

    public function setDbUserName($dbUserName) {
        $this->dbUserName = $dbUserName;
    }

    public function getDefaultController() {
        return $this->defaultCnA['controller'];
    }

    public function setDefaultController($controller) {
        $this->defaultCnA['controller'] = ucfirst(strtolower($controller));
    }

    public function getDefaultAction() {
        return $this->defaultCnA['action'];
    }

    public function setDefaultAction($action) {
        $this->defaultCnA['action'] = ucfirst(strtolower($action));
    }

    public function getErrorController() {
        return $this->errorCnA['controller'];
    }

    public function setErrorController($controller) {
        $this->errorCnA['controller'] = ucfirst(strtolower($controller));
    }

    public function getErrorAction() {
        return $this->errorCnA['action'];
    }

    public function setErrorAction($action) {
        $this->errorCnA['action'] = ucfirst(strtolower($action));
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function setRoutes($routes) {
        $this->routes = $routes;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($state) {
        if ($state != self::DEVELOPMENT && $state != self::PRODUCTION)
            return;
        $this->state = $state;
        if ($state == self::PRODUCTION)
            Vortex_Logger::level(Vortex_Logger::ERROR);
        else
            Vortex_Logger::level(Vortex_Logger::DEBUG);
    }

    public function isProduction() {
        return $this->state == self::PRODUCTION;
    }

    public function setProduction($flag = true) {
        if ($flag === true)
            $this->setState(self::PRODUCTION);
        else if ($flag === false)
            $this->setState(self::DEVELOPMENT);
    }

    public function getViewExtension() {
        return $this->viewExtension;
    }

    public function setViewExtension($viewExtension) {
        $this->viewExtension = $viewExtension;
    }

    public function enableLayouts($bool = true) {
        $this->isLayouts = ((bool) $bool);
    }

    public function isLayouts() {
        return $this->isLayouts;
    }

    public function getDefaultLayout() {
        return $this->defaultLayout;
    }

    public function setDefaultLayout($defaultLayout) {
        if (in_array($defaultLayout, $this->layouts))
            $this->defaultLayout = $defaultLayout;
    }

    public function registerLayout($layout) {
        if (is_file(APPLICATION_PATH . '/views/layouts/' . $layout . '.' . $this->viewExtension)) {
            if (count($this->layouts) == 0)
                $this->defaultLayout = $layout;
            array_push($this->layouts, $layout);
        }
    }

    public function getLayouts() {
        return $this->layouts;
    }
} 