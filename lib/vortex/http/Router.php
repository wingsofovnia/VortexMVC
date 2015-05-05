<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 07:51
 */

namespace vortex\http;


use vortex\utils\Config;
use vortex\utils\Logger;

class Router {
    const ROUTER_ERROR_ROUTE_HEAD = 'ERROR';

    const ROUTER_CONFIG_FILE = 'routes.config';
    const ROUTER_CONFIG_SPLIT_DELIMITER = '/\s+/';
    const ROUTER_CONFIG_COMMENT_SYMBOL = '#';
    const ROUTER_TARGET_DELIMITER = '::';
    private $routes;

    private $controller = null;
    private $action = null;
    private $params = array();

    public function __construct(Request $request, $file = Router::ROUTER_CONFIG_FILE) {
        if ($request == null)
            throw new \InvalidArgumentException("null request");

        $this->routes = $this->parse($file);
        $this->process($request);
    }

    private function parse($file) {
        $filePath = APPLICATION_PATH . '/' . $file;
        if (!is_file($filePath))
            throw new \InvalidArgumentException("Bad route file");

        $routes = array();
        $configFile = new \SplFileObject($filePath);
        while (!$configFile->eof()) {
            $line = $configFile->fgets();
            $line = trim($line);

            $parts = preg_split(Router::ROUTER_CONFIG_SPLIT_DELIMITER, $line);
            if (substr($line, 0, 1) === Router::ROUTER_CONFIG_COMMENT_SYMBOL || empty($line))
                continue;

            if (0 === strpos($line, Router::ROUTER_ERROR_ROUTE_HEAD)) {
                $target = explode(Router::ROUTER_TARGET_DELIMITER, $parts[1]);
                $routes[$parts[0]] = array('controller' =>  $target[0],
                                           'action'     =>  $target[1]);
                continue;
            }

            if (count($parts) != 3)
                throw new RouterException('File has bad format at line (count($parts) = ' . count($parts). ') = ' . $line);

            $target = explode(Router::ROUTER_TARGET_DELIMITER, $parts[2]);
            $pattern = '/^' . str_replace('/', '\/', $parts[1]) . '(\/|$)/i';

            $routes[$parts[0]][] = array('pattern'    =>  $pattern,
                                         'controller' =>  $target[0],
                                         'action'     =>  $target[1]);
        }
        $configFile = null;
        return $routes;
    }

    private function process(Request $request) {
        $reqMethod = $request->getMethod();
        if (!isset($this->routes[$reqMethod]))
            return;

        $reqUrl = $this->cleanURL($request->getRawUrl());

        $methodMappings = $this->routes[$request->getMethod()];
        foreach ($methodMappings as $route) {
            $pattern = $route['pattern'];
            $controller = $route['controller'];
            $action = $route['action'];
            if (preg_match($pattern, $reqUrl, $params)) {
                /* Defining controller and action */
                $this->controller = $controller;
                $this->action = $action;

                /* Other part of url may contain params. Lets try to merge them */
                $paramsLine = preg_replace($pattern, '', $reqUrl);
                $paramsLine = trim($paramsLine, '/');
                $args = explode('/', $paramsLine);
                $args = array_filter($args);
                if (count($args) == 0) {
                    $args = array_values($args);
                    $length = count($args);
                    $params = array();
                    for ($i = 0; $i < $length; $i++) {
                        if (($i + 1) < $length) {
                            $params[$args[$i]] = $args[$i + 1];
                            $i++;
                        }
                    }
                    $this->params = array_merge($this->params, $params);
                }

                Logger::debug('Predefined route = {controller: ' . $this->controller . ', action = ' . $this->action . '}');
                return;
            }
        }
        if (!Config::isProduction())
            throw new RouterException('No predefined route has been found');

        Logger::error("No route has been found!");
        $this->controller = $this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]['controller'];
        $this->action = $this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]['action'];
        $request->addParam('message', "No route has been found!");
    }

    /**
     * Service method to clean URL from HOST name and special chars
     * @param string $url url string to clean
     * @return string a cleaned url
     */
    private function cleanURL($url) {
        $url = utf8_decode(urldecode(($url)));
        $subPath = Config::getInstance()->application->subpath('');

        if (strlen($subPath) > 0 && substr($url, 0, strlen($subPath)) == $subPath)
            $url = substr($url, strlen($subPath));
        $url = preg_replace('/[^A-Za-z0-9\-\/]/', '', $url);
        if (strlen($url) == 0)
            return '\\';
        return strtolower($url);
    }

    /**
     * Gets a name of controller
     * @return string controller's name
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Gets a name of action
     * @return string action's name
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Gets a name of error controller
     * @return string controller's name
     */
    public function getErrorController() {
        return isset($this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]) ?
                        $this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]['controller'] : null;
    }

    /**
     * Gets a name of error action
     * @return string action's name
     */
    public function getErrorAction() {
        return isset($this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]) ?
            $this->routes[Router::ROUTER_ERROR_ROUTE_HEAD]['action'] : null;
    }

    /**
     * Gets params, parsed from url
     * @return array params
     */
    public function getParams() {
        return $this->params;
    }
} 