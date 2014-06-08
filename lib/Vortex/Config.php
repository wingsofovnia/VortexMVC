<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Config
 * This class contains all available configs of engine.
 *
 * Note! Config class should be configured before engine start,
 * than all changes will be ignored.
 */
final class Vortex_Config {
    private static $_instance = null;

    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'pro';

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

    private $MetaObjectTypesTable;
    private $MetaObjectTable;
    private $MetaAttributesTable;
    private $MetaParamsTable;

    /**
     * Init constructor with some default values
     */
    protected function __construct() {
        $this->setState(Vortex_Config::DEVELOPMENT);
        $this->setDefaultController('index');
        $this->setDefaultAction('index');

        $this->setErrorController('error');
        $this->setErrorAction('index');

        $this->setViewExtension('tpl');
        $this->layouts = array();
        $this->enableLayouts(false);

        $this->setMetaAttributesTable('Meta_Attributes');
        $this->setMetaObjectTable('Meta_Objects');
        $this->setMetaObjectTypesTable('Meta_ObjectTypes');
        $this->setMetaParamsTable('Meta_Params');
    }

    protected function __clone() { }

    /**
     * Singleton instance getter
     * @return Vortex_Config instance
     */
    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Gets a current PDO driver
     * @return string name of PDO driver
     */
    public function getDbPDODriver() {
        return $this->dbPDODriver;
    }

    /**
     * Sets a PDO driver
     * @param string $dbPDODriver a name of PDO driver
     * @throws Vortex_Exception_DBError if such driver is not available
     */
    public function setDbPDODriver($dbPDODriver) {
        $availableDrivers = PDO::getAvailableDrivers();
        if (!in_array($dbPDODriver, $availableDrivers))
            throw new Vortex_Exception_DBError("No <$dbPDODriver> found!");
        $this->dbPDODriver = $dbPDODriver;
    }

    /**
     * Gets current DB host
     * @return string host address
     */
    public function getDbHost() {
        return $this->dbHost;
    }

    /**
     * Sets current DB host
     * @param string $dbHost host address
     * @throws Vortex_Exception_IllegalArgument if param is empty
     */
    public function setDbHost($dbHost) {
        if (empty($dbHost))
            throw new Vortex_Exception_IllegalArgument('Db host name should be not empty!');
        $this->dbHost = $dbHost;
    }

    /**
     * Gets a current site database
     * @return string db name
     */
    public function getDbDataBase() {
        return $this->dbDataBase;
    }

    /**
     * Sets a current site database
     * @param string $dbDataBase db name
     */
    public function setDbDataBase($dbDataBase) {
        $this->dbDataBase = $dbDataBase;
    }

    /**
     * Gets a stetted db password
     * @return string db password
     */
    public function getDbPassword() {
        return $this->dbPassword;
    }

    /**
     * Sets a password for db connection
     * @param string $dbPassword db password
     */
    public function setDbPassword($dbPassword) {
        $this->dbPassword = $dbPassword;
    }

    /**
     * Gets a current db username
     * @return string db username
     */
    public function getDbUserName() {
        return $this->dbUserName;
    }

    /**
     * Sets a db connection username
     * @param string $dbUserName db username
     */
    public function setDbUserName($dbUserName) {
        $this->dbUserName = $dbUserName;
    }

    /**
     * Gets a default controller for root URL '/'
     * @return string controller name
     */
    public function getDefaultController() {
        return $this->defaultCnA['controller'];
    }

    /**
     * Sets a default controller for root URL '/'
     * @param string $controller controller name
     */
    public function setDefaultController($controller) {
        $this->defaultCnA['controller'] = ucfirst(strtolower($controller));
    }

    /**
     * Gets a default action for root URL '/'
     * @return string action name
     */
    public function getDefaultAction() {
        return $this->defaultCnA['action'];
    }

    /**
     * Sets a default action for root URL '/'
     * @param string $action action name
     */
    public function setDefaultAction($action) {
        $this->defaultCnA['action'] = ucfirst(strtolower($action));
    }

    /**
     * Gets an error controller for displaying errors
     * @return string controller name
     */
    public function getErrorController() {
        return $this->errorCnA['controller'];
    }

    /**
     * Sets an error controller for displaying errors
     * @param string $controller controller name
     */
    public function setErrorController($controller) {
        $this->errorCnA['controller'] = ucfirst(strtolower($controller));
    }

    /**
     * Gets an error action for displaying errors
     * @return string action name
     */
    public function getErrorAction() {
        return $this->errorCnA['action'];
    }

    /**
     * Gets an error action for displaying errors
     * @param string $action action name
     */
    public function setErrorAction($action) {
        $this->errorCnA['action'] = ucfirst(strtolower($action));
    }

    /**
     * Gets all defined routes
     * @return array an array of routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Sets a routes array
     * @param array $routes an array of routes
     */
    public function setRoutes($routes) {
        $this->routes = $routes;
    }

    /**
     * Returns current state of site
     * @return string div/prod current state
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Sets current state of site
     * @param string $state const Vortex_Config::DEVELOPMENT / Vortex_Config::PRODUCTION
     */
    public function setState($state) {
        if ($state != self::DEVELOPMENT && $state != self::PRODUCTION) return;
        $this->state = $state;
        if ($state == self::PRODUCTION) Vortex_Logger::level(Vortex_Logger::ERROR); else
            Vortex_Logger::level(Vortex_Logger::DEBUG);
    }

    /**
     * Checks if state is PRODUCTION
     * @return bool true, if state is Vortex_Config::PRODUCTION
     */
    public function isProduction() {
        return $this->state == self::PRODUCTION;
    }

    /**
     * Sets a production state of site
     * Variation of @see setState
     * @param bool $flag true, for PRODUCTION state
     */
    public function setProduction($flag = true) {
        if ($flag === true) $this->setState(self::PRODUCTION); else if ($flag === false) $this->setState(self::DEVELOPMENT);
    }

    /**
     * Gets a view template file extension (default: tpl)
     * @return string view file extension
     */
    public function getViewExtension() {
        return $this->viewExtension;
    }

    /**
     * Sets a view template file extension (default: tpl)
     * @param string $viewExtension view file extension
     */
    public function setViewExtension($viewExtension) {
        $this->viewExtension = $viewExtension;
    }

    /**
     * Switch for layout system
     * @param bool $bool true, if you want to use layout system
     */
    public function enableLayouts($bool = true) {
        $this->isLayouts = ((bool)$bool);
    }

    /**
     * Checks if layout system enabled
     * @return bool true, if layout system enabled
     */
    public function isLayouts() {
        return $this->isLayouts;
    }

    /**
     * Returns a default layout's name
     * @return string name of layout tpl
     */
    public function getDefaultLayout() {
        return $this->defaultLayout;
    }

    /**
     * Sets a default layout
     * @param string $defaultLayout name of layout
     */
    public function setDefaultLayout($defaultLayout) {
        if (in_array($defaultLayout, $this->layouts)) $this->defaultLayout = $defaultLayout;
    }

    /**
     * Register new layout template
     * @param string $layout name of layout
     */
    public function registerLayout($layout) {
        if (is_file(APPLICATION_PATH . '/views/layouts/' . $layout . '.' . $this->viewExtension)) {
            if (count($this->layouts) == 0) $this->defaultLayout = $layout;
            array_push($this->layouts, $layout);
        }
    }

    /**
     * Returns all registered layouts
     * @return array array of layouts
     */
    public function getLayouts() {
        return $this->layouts;
    }

    public function setMetaAttributesTable($MetaAttributesTable) {
        $this->MetaAttributesTable = $MetaAttributesTable;
    }

    public function setMetaObjectTable($MetaObjectTable) {
        $this->MetaObjectTable = $MetaObjectTable;
    }

    public function setMetaObjectTypesTable($MetaObjectTypesTable) {
        $this->MetaObjectTypesTable = $MetaObjectTypesTable;
    }

    public function setMetaParamsTable($MetaParamsTable) {
        $this->MetaParamsTable = $MetaParamsTable;
    }

    public function getMetaAttributesTable() {
        return $this->MetaAttributesTable;
    }

    public function getMetaObjectTable() {
        return $this->MetaObjectTable;
    }

    public function getMetaObjectTypesTable() {
        return $this->MetaObjectTypesTable;
    }

    public function getMetaParamsTable() {
        return $this->MetaParamsTable;
    }
} 