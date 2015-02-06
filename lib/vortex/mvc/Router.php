<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 15-Jun-14
 */

namespace vortex\mvc;

use vortex\http\Request;
use vortex\utils\Config;
use vortex\utils\Logger;

/**
 * Class Router defines, what controller and it's action should be executed, based on URL string
 * and predefined routers/redirects with annotations of controller's actions
 */
class Router {
    private $url;
    private $req;

    private $isMapping;
    private $mappings;

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
        $this->isMapping = Config::getInstance()->router->mapping->enabled(true);

        if ($this->isMapping)
            $this->mappings = $this->parseMappings();

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
        if ($this->isMapping && $this->mappings) {
            foreach ($this->mappings as $pattern => $route) {
                if (preg_match($pattern, $this->url, $params)) {
                    /* Defining controller and action */
                    $this->controller = $route['controller'];
                    $this->action = $route['action'];

                    /* Other part of url may contain params. Lets try to merge them */
                    $params = $this->explodeUrl(str_replace(array_shift($params), '', $this->url));
                    $this->mergeUrlParams($params);
                    Logger::debug('Predefined route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
                    return;
                }
            }
        }

        $args = $this->explodeUrl();
        if (!$args)
            return;

        $this->controller = $args[0];
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
        $url = utf8_decode(urldecode(($url)));
        $subPath = Config::getInstance()->router->subpath('');

        if (strlen($subPath) > 0 && substr($url, 0, strlen($subPath)) == $subPath)
            $url = substr($url, strlen($subPath));

        $url = preg_replace('/[^A-Za-z0-9\-\/]/', '', $url);
        return strtolower($url);
    }

    /**
     * Parses predefined routes from application.ini
     * @return array a set of routes
     */
    private function parseMappings() {
        $rawRoutes = Config::getInstance()->router->mappings;
        $mappings = array();

        foreach ($rawRoutes as $route) {
            $pattern = '/^' . str_replace('/', '\/', $route['pattern']) . '(\/|$)/i';
            $mappings[$pattern] = array(
                'controller'    =>  $route['controller'],
                'action'        =>  isset($route['action']) ? $route['action'] : 'index'
            );
        }

        return $mappings;
    }

    /**
     * Changes master switch of using predefined routes
     * @param bool $isMapping
     */
    public function setIsMapping($isMapping) {
        $this->isMapping = !!$isMapping;
    }
}