<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 15-Jun-14
 * Time: 10:40
 */

namespace Vortex;


use Vortex\Cache\Cache;
use Vortex\Cache\CacheFactory;
use Vortex\Utils\Service;
use Vortex\Utils\Text;

class Router {
    private $url;

    private $routes;

    private $controller;
    private $action;
    private $params;


    public function __construct($url = '/') {
        $cleaned = $this->cleanURL($url);
        if (empty($cleaned))
            $cleaned = '/';
        $this->setUrl($cleaned);
        $this->controller = ucfirst(strtolower(Config::getInstance()->controller->default));
        $this->action = strtolower(Config::getInstance()->action->default);
        $this->params = array();
    }

    /**
     * Gets a url that need to be routed
     * @return string url
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Changes url to parse
     * @param string $url a url
     * @throws \InvalidArgumentException if param $url is empty
     */
    public function setUrl($url) {
        if (empty($url))
            throw new \InvalidArgumentException('Param $url should be not empty!');
        $this->url = $url;
    }

    /**
     * Gets a name of controller parsed from url
     * @return string controller's name
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Gets a name of action parsed from url
     * @return string action's name
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Gets params, parsed from url
     * @return array params
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Runs router's processing param1/val1/param2/val2
     */
    public function parse() {
        $this->parseRoutes();

        $predefined = false;
        foreach ($this->routes['mapping'] as $route => $path) {
            if (Text::startsWith($this->url, $route)) {
                $this->controller = ucfirst(strtolower($path['controller']));
                $this->action = strtolower($path['action']);
                Logger::debug('Predefined route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
                $predefined = true;
                break;
            }
        }

        if (!$predefined) {
            $this->parseURL();
            Logger::debug('Parsed route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
        }


        if (isset($this->routes['redirect'][$this->controller][$this->action])) {
            $ctrl = $this->controller;
            $actn = $this->action;
            $this->controller = $this->routes['redirect'][$ctrl][$actn]['controller'];
            $this->action = $this->routes['redirect'][$ctrl][$actn]['action'];
            Logger::debug('Redirected to = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
        }
    }

    /**
     * Parses routes form controller's annotations
     */
    private function parseRoutes() {
        $cache = CacheFactory::getFactory(CacheFactory::FILE_DRIVER, array(
            'namespace' => 'vf_router',
            'lifetime'  => Cache::UNLIMITED_LIFE_TIME
        ));

        $routes = $cache->load('annotations');
        if ($routes) {
            $this->routes = $routes;
            return;
        }

        $appNamespace = 'Application\Controllers\\';

        $routes = array('mapping' => array(), 'redirect' => array());
        foreach(glob(APPLICATION_PATH.'/controllers/*Controller.php') as $file) {
            $controllerName = basename($file, "Controller.php");
            $controller = $appNamespace . $controllerName . 'Controller';
            $methodsAnnotations = Annotation::getAllMethodsAnnotations($controller);

            foreach ($methodsAnnotations as $action => $data) {
                if (!Text::endsWith($action, 'Action'))
                    continue;
                $action = str_replace('Action', '', $action);
                if (isset($data[Annotation::REDIRECT])) {
                    $routes['redirect'][$controllerName][$action] = array(
                        'controller'    =>  $data[Annotation::REDIRECT][0],
                        'action'        =>  $data[Annotation::REDIRECT][1]
                    );
                }
                if (isset($data[Annotation::REQUEST_MAPPING])) {
                    $routes['mapping'][$data[Annotation::REQUEST_MAPPING][0]] = array(
                        'controller'    =>  $controllerName,
                        'action'        =>  $action
                    );
                }
            }
        }
        $cache->save('annotations', $routes);
        Logger::debug($routes);
        $this->routes = $routes;
    }

    /**
     * Parses the requested controller and action in URL
     */
    private function parseURL() {
        $args = explode('/', $this->url);
        if (count($args) == 0)
            return;
        $args = array_filter($args);
        $args = array_map('trim', $args);
        $args = array_map('strtolower', $args);
        $args = array_values($args);
        $num = count($args);
        if ($num > 0) {
            $this->controller = ucfirst($args[0]);
            if ($num > 1) {
                $this->action = $args[1];
                if ($num > 2) {
                    $this->params = array_merge($this->params, $this->parseUrlParams(array_slice($args, 2)));
                }
            }
        }
    }

    public function parseUrlParams(array $url) {
        $params = array();
        $length = count($url);
        for ($i = 0; $i < $length; $i++) {
            if (($i + 1) < $length) {
                $params[$url[$i]] = $url[$i + 1];
                $i++;
            }
        }

        return $params;
    }

    /**
     * Service method to clean URL from HOST name and special chars
     * @param string $url url string to clean
     * @return string a cleaned url
     */
    private function cleanURL($url) {
        //$url = urldecode($this->url);
        if (strpos($url, $_SERVER['SERVER_NAME']) !== false)
            $url =  substr($url, strpos($url, $_SERVER['SERVER_NAME']));
        if (strlen($url) == 1)
            return '/';
        $url = rtrim($url, '/');
        $url = preg_replace('/\s+/', '', $url);
        $url = preg_replace('/[^A-Za-z0-9\-\/]/', '', $url);
        return $url;
    }
} 