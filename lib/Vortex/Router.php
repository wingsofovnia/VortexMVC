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
use Vortex\Utils\Text;

/**
 * Class Router
 * This class defines, what controller and it's action should be executed, based on URL string
 * and predefined routers/redirects with annotations of controller's actions
 *
 * @package Vortex
 */
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
        /* Reading for predefined routes and redirects from annotations */
        $this->parseRoutes();

        /* First, looking if some action requested mapping for this url */
        $predefined = false;
        foreach ($this->routes['mapping'] as $route => $path) {
            if (Text::startsWith($this->url, $route)) {
                /* Defining controller and action */
                $this->controller = $path['controller'];
                $this->action = $path['action'];

                /* Other part of url may contain params. Trying to parse it */
                $this->mergeUrlParams(explode('/', ltrim(str_replace($route,'',$this->url), '/')));

                Logger::debug('Predefined route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
                $predefined = true;
                break;
            }
        }

        /* If it not predefined, we should parse ours url and determine what to open */
        if (!$predefined) {
            $this->parseURL();
            Logger::debug('Parsed route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
        }

        /* In the end, our identified action can have @Redirect annotation. Checking this.. */
        if (isset($this->routes['redirect'][$this->controller][$this->action])) {
            $ctrl = $this->controller;
            $this->controller = $this->routes['redirect'][$ctrl][$this->action]['controller'];
            $this->action = $this->routes['redirect'][$ctrl][$this->action]['action'];
            Logger::debug('Redirected to = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
        }
    }

    /**
     * Parses routes form controller's annotations
     */
    private function parseRoutes() {
        /* Init cache object */
        $cache = CacheFactory::getFactory(CacheFactory::FILE_DRIVER, array(
            'namespace' => 'vf_router',
            'lifetime'  => Cache::UNLIMITED_LIFE_TIME
        ));

        /* Checking if annotations have already parsed */
        $routes = $cache->load('annotations');
        if ($routes) {
            $this->routes = $routes;
            return;
        }

        /* Asking for all annotations for classes and their methods in dir Controllers */
        $dir = APPLICATION_PATH.'/controllers/';
        $filter = '*Controller';
        $classNamespace = 'Application\Controllers\\';
        $annotations = Annotation::getAllClassFilesAnnotations($dir, $filter, $classNamespace);

        /* Retrieving Annotation::REQUEST_MAPPING and Annotation::REDIRECT data */
        $routes = array('mapping' => array(), 'redirect' => array());
        foreach ($annotations as $className => $data) {
            $controller = str_replace('Controller', '', $className);
            foreach ($data['methods'] as $methodName => $methodAnnotations) {
                if (!Text::endsWith($methodName, 'Action'))
                    continue;
                $action = str_replace('Action', '', $methodName);

                if (isset($methodAnnotations[Annotation::REDIRECT])) {
                    $routes['redirect'][$controller][$action] = array(
                        'controller'    =>  $methodAnnotations[Annotation::REDIRECT][0],
                        'action'        =>  $methodAnnotations[Annotation::REDIRECT][1]
                    );
                }

                if (isset($methodAnnotations[Annotation::REQUEST_MAPPING])) {
                    $routes['mapping'][$methodAnnotations[Annotation::REQUEST_MAPPING][0]] = array(
                        'controller'    =>  $controller,
                        'action'        =>  $action
                    );
                }
            }
        }

        /* Caching */
        $cache->save('annotations', $routes);

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
                    $this->mergeUrlParams(array_slice($args, 2));
                }
            }
        }
    }

    /**
     * Merges an array of params from exploded url to Router params
     * @param array $url array of [param1, val1, param2, val2...]
     */
    private function mergeUrlParams(array $url) {
        $length = count($url);
        if (!$length)
            return;
        $params = array();
        for ($i = 0; $i < $length; $i++) {
            if (($i + 1) < $length) {
                $params[$url[$i]] = $url[$i + 1];
                $i++;
            }
        }
        $this->params = array_merge($this->params, $params);
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