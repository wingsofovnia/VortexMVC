<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 19:26
 */

$configs = Vortex_Config::getInstance();

$configs->setProduction(false);

$configs->setDefaultController('index');
$configs->setDefaultAction('index');

$configs->setErrorController('index');
$configs->setErrorAction('index');


$routes = array();
$routes[0] = array(
    'url'           =>  '/testrec',
    'controller'    =>  'index',
    'action'        =>  'index'
);
$configs->setRoutes($routes);

$configs->setDbPDODriver('mysql');
$configs->setDbHost('localhost');
$configs->setDbUserName('root');
$configs->setDbPassword('');
$configs->setDbDataBase('VortexMVC');