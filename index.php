<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

define('ROOT_PATH', realpath(dirname(__FILE__)));
define('LIB_PATH', ROOT_PATH . '/lib');
define('APP_PATH', ROOT_PATH . '/application');

define('APP_LAYOUTS_PATH', APP_PATH . '/views/layouts');
define('APP_TEMPLATES_PATH', APP_PATH . '/views/templates');

define('APP_WIDGET_NAMESPACE', 'application\widgets\\');
define('APP_WIDGET_POSTFIX', 'Widget');

define('APP_CONTROLLER_POSTFIX', 'Controller');

require_once LIB_PATH . '/vortex/Application.php';
use vortex\Application;

Application::init();
Application::dispatch();
Application::display();