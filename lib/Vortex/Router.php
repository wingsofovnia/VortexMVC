<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 15-Jun-14
 *
 * @package Vortex
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
    const ROUTE_METHOD_ALL = -1;

    private $url;
    private $req;

    private $routes;

    private $controller;
    private $action;
    private $params;


    /**
     * Inits a router
     * @param Request $request
     */
    public function __construct($request) {
        $this->req = $request;
        $this->setUrl($this->req->getRawUrl());

        $this->controller = Config::getInstance()->controller->default;
        $this->action = Config::getInstance()->action->default;
        $this->params = array();
    }

    /**
     * Gets a url that should be routed
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
        $this->url = $this->cleanURL($url);
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
        Logger::debug($this->url);
        foreach ($this->routes['mapping'] as $method => $routes) {
            if ($method != self::ROUTE_METHOD_ALL && strcasecmp($method, $this->req->getMethod()) != 0)
                continue;

            foreach ($routes as $route) {
                $pattern = $route['pattern'];
                if (preg_match($pattern, $this->url, $params)) {
                    /* Defining controller and action */
                    $this->controller = $route['controller'];
                    $this->action = $route['action'];

                    /* Other part of url may contain params. Lets try to merge them */
                    $params = $this->explodeUrl(str_replace(array_shift($params), '', $this->url));
                    $this->mergeUrlParams($params);

                    Logger::debug('Predefined route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
                    $predefined = true;
                    break;
                }
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
        $cache = CacheFactory::build(CacheFactory::FILE_DRIVER, array(
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
                    if (!isset($methodAnnotations[Annotation::REQUEST_MAPPING][0]))
                        throw new \InvalidArgumentException("Bad REQUEST_MAPPING annotation");

                    $pattern =  '/^' . str_replace('/', '\/', $methodAnnotations[Annotation::REQUEST_MAPPING][0]) . '(\/|$)/i';
                    $method = isset($methodAnnotations[Annotation::REQUEST_MAPPING][1]) ?
                        $methodAnnotations[Annotation::REQUEST_MAPPING][1] : self::ROUTE_METHOD_ALL;

                    $routes['mapping'][$method][] = array(
                        'pattern'       =>  $pattern,
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
        $args = $this->explodeUrl();
        if (!$args)
            return;

        $this->controller = ucfirst($args[0]);
        if (isset($args[1]))
            $this->action = $args[1];

        if (isset($args[2]))
            $this->mergeUrlParams(array_slice($args, 2));
    }

    /**
     * Explodes a url into parts
     * @param string $url url to parse (default - $this->url)
     * @return array|false exploded url
     */
    private function explodeUrl($url = null) {
        if (!$url)
            $url = $this->url;
        $args = explode('/', $url);
        $args = array_filter($args);
        if (count($args) == 0)
            return false;
        $args = array_values($args);
        return $args;
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
        $url = html_entity_decode($url);
        if (strpos($url, $_SERVER['SERVER_NAME']) !== false)
            $url =  substr($url, strpos($url, $_SERVER['SERVER_NAME']));
        if (strlen($url) == 1)
            return '/';
        $url = rtrim($url, '/');
        $url = preg_replace('/\s+/', '', $url);
        $url = preg_replace('/[^A-Za-z0-9\-\/]/', '', $url);
        return strtolower($url);
    }
}