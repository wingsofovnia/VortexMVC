<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

define('ROOT_PATH', realpath(dirname(__FILE__)));
define('LIB_PATH', ROOT_PATH . '/lib');
define('APPLICATION_PATH', ROOT_PATH . '/application');
require_once LIB_PATH . '/vortex/application/Application.php';

use vortex\application\Application;
Application::run();