<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:17
 */

define('ROOT_PATH', realpath(dirname(__FILE__)));
define('LIB_PATH', ROOT_PATH . '/lib');
define('APPLICATION_PATH', ROOT_PATH . '/application');
require_once LIB_PATH . '/Vortex/Application.php';

use \Vortex\Application as Vortex_Application;
Vortex_Application::run();