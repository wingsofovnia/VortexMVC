<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 09-Apr-16
 * Time: 18:21
 */

namespace vortex\routing;

use vortex\http\Request;

/**
 * Class Router is used to translate input URL to specific controller and action names using routing table.
 * @package vortex\routing
 */
class Router {
    const ROUTER_CONFIG_SPLIT_DELIMITER = '/\s+/';
    const ROUTER_CONFIG_COMMENT_SYMBOL = '#';
    const ROUTER_TARGET_DELIMITER = '::';

    private $routes = array();

    public function addRule(Rule $route) {
        if (empty($route))
            throw new \InvalidArgumentException("Route param must not be empty");

        if (!$this->hasRule($route))
            $this->routes[] = $route;
    }

    public function parseRules(\SplFileObject $file, $rewriteRoutes = false) {
        if ($rewriteRoutes === true)
            $this->routes = array();

        $_lineNum = 0;
        while (!$file->eof()) {
            $line = $file->fgets();
            $line = trim($line);
            $_lineNum++;

            if (substr($line, 0, 1) === Router::ROUTER_CONFIG_COMMENT_SYMBOL || empty($line))
                continue;

            $parts = preg_split(Router::ROUTER_CONFIG_SPLIT_DELIMITER, $line);
            if (count($parts) != 3)
                throw new RouterException('File has bad format (3 params are expected) at line #' . $_lineNum . ': ' . $line);

            $pattern = '/^' . str_replace('/', '\/', $parts[1]) . '(\/|$)/i';
            $target = explode(Router::ROUTER_TARGET_DELIMITER, $parts[2]);
            if (count($target) != 2)
                throw new RouterException('File has bad format (' . self::ROUTER_TARGET_DELIMITER . ' divided target is expected) at line #' . $_lineNum . ': ' . $line);

            $this->addRule(new Rule($parts[0], $pattern, $target[0], $target[1]));
        }
    }

    public function removeRule(Rule $route) {
        if (empty($route))
            throw new \InvalidArgumentException("Route param must not be empty");

        if (($key = array_search($route, $this->routes)) !== FALSE)
            unset($this->routes[$key]);
    }

    public function hasRule(Rule $route) {
        foreach ($this->routes as $_route) {
            if ($_route == $route)
                return true;
        }
        return false;
    }



    public function route(Request $request) {
        $requestMethod = $request->getMethod();
        $requestURL = $request->getURL();

        /** @var $_route Rule */
        foreach ($this->routes as $_route) {
            if ($_route->getMethod() == strtolower($requestMethod)
                && preg_match($_route->getPattern(), $requestURL, $params)) {

                $paramURL = preg_replace($_route->getPattern(), '', $requestURL);
                $paramURL = trim($paramURL, '/');
                $paramArgs = explode('/', $paramURL);
                $paramArgs = array_filter($paramArgs);

                $params = array();
                if (count($paramArgs) == 0) {
                    $paramArgs = array_values($paramArgs);
                    $length = count($paramArgs);
                    for ($i = 0; $i < $length; $i++) {
                        if (($i + 1) < $length) {
                            $params[$paramArgs[$i]] = $paramArgs[$i + 1];
                            $i++;
                        }
                    }
                }

                return new Route($_route->getAction(),
                                 $_route->getController(),
                                 $params);
            }
        }

        return NULL;
    }
} 