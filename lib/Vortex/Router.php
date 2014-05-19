<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:29
 */

class Vortex_Router {
    private $controller;
    private $action;
    private $params;
    private $url;
    private $routes;

    public function __construct() {
        $this->controller = 'index';
        $this->action = 'index';
        $this->params = array();
        $this->url = '';
        $this->routes = array();
    }

    public function parse() {
        if (!empty($this->url)) {
            if (isset($this->routes[$this->url])) {
                $this->controller = $this->routes[$this->url]['controller'];
                $this->action = $this->routes[$this->url]['action'];
                if (isset($this->routes[$this->url]['params']))
                    $this->params = $this->routes[$this->url]['params'];
            } else {
                $this->parseURL();
            }
        }
    }

    public function registerRoute($route) {
        $required = array('url', 'controller', 'action');
        if (count(array_intersect_key(array_flip($required), $route)) === count($required)) {
            $url = $route['url'];
            unset($route['url']);
            $this->routes[$url] = $route;
        }
        return $this;
    }

    public function unregisterRoute($url) {
        if (array_key_exists($url, $this->routes))
            unset($this->routes[$url]);
        return $this;
    }

    private function parseURL() {
        $args = explode('/', $this->url);
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
                    for ($i = 2; $i < $num; $i++) {
                        if (($i + 1) < $num) {
                            $this->params[$args[$i]] = $args[$i + 1];
                            $i++;
                        }
                    }
                }
            }
        }
    }

    public function getAction() {
        return $this->action;
    }

    public function getController() {
        return $this->controller;
    }

    public function getParams() {
        return $this->params;
    }

    public function setUrl($url) {
        $this->url = $url;
        $this->cleanURL();
    }

    public function getUrl() {
        return $this->url;
    }

    private function cleanURL() {
        $url = urldecode($this->url);
        if (strpos($url, $_SERVER['SERVER_NAME']) !== false)
            $url =  substr($url, strpos($url, $_SERVER['SERVER_NAME']));
        if (strlen($url) > 1)
            $url = rtrim($url, '/');
        $url = preg_replace('/\s+/', '', $url);
        $url = preg_replace('/[^A-Za-z0-9\-]/', '', $url);
        return $url;
    }
} 