<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace vortex\http;
use vortex\routing\Route;
use vortex\utils\Config;

/**
 * Class Request implements a wrapper of HTTP Request with additional,
 * extended functionality
 * @package vortex\http
 */
class Request {
    private $route;
    private $get;
    private $post;
    private $cookies;
    private $method;

    public function __construct() {
        $this->post = array_map('trim', $_POST);
        $this->get = array_map('trim', $_GET);
        $this->cookies = array_map('trim', $_COOKIE);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Sets a Route information from Router
     * @param Route $route
     * @throws \InvalidArgumentException if param $route is empty
     */
    public function setRoute(Route $route) {
        if (empty($route))
            throw new \InvalidArgumentException('Param $route should be not empty!');

        $this->route = $route;
    }

    /**
     * @return Route
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * Gets a POST value by key from request
     * @param string $key a key
     * @param null|mixed $default value that will be settled, if no POST key-value found
     * @return string a POST value
     */
    public function getPost($key, $default = null) {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    /**
     * Gets a GET value by key from request
     * @param string $key a key
     * @param null|mixed $default value that will be settled, if no GET key-value found
     * @return string a GET value
     */
    public function getGet($key, $default = null) {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    /**
     * Gets a COOKIE value by key from request
     * @param string $key a key
     * @param null|mixed $default value that will be settled, if no COOKIE found
     * @return string a COOKIE's value
     */
    public function getCookie($key, $default = null) {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : $default;
    }


    /**
     * Checks if request method is POST
     * @return bool true, if POST, else - false
     */
    public function isPost() {
        return 'POST' == $this->getMethod();
    }

    /**
     * Gets a name of a method of a request from it's headers
     * @return string METHOD name
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Checks if request method is GET
     * @return bool true, if GET, else - false
     */
    public function isGet() {
        return 'GET' == $this->getMethod();
    }

    /**
     * Checks if request method is an ASYNC request
     * @return bool true, if it's a XmlHttpReques, else - false
     */
    public function isXMLHttpRequest() {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }

    /**
     * Gets a value of header from request
     * @param string $header a name of header
     * @return string|null a headers value, if there is no such header - null
     * @throws \InvalidArgumentException if $header name is empty
     */
    public function getHeader($header) {
        if (empty($header))
            throw new \InvalidArgumentException('An HTTP header name is required');
        $httpHeader = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$httpHeader])) {
            return $_SERVER[$httpHeader];
        }

        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders[$header]))
                return $requestHeaders[$header];

            $header = strtolower($header);
            foreach ($requestHeaders as $key => $value)
                if (strtolower($key) == $header)
                    return $value;
        }
        return null;
    }

    /**
     * Checks if user uses secure connection (HTTPS)
     * @return bool true, if HTTPS
     */
    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Gets clean URL without HOST name and special chars
     * @return string a cleaned request url
     */
    public function getURL() {
        $url = utf8_decode(urldecode(($this->getRawUrl())));
        $subPath = Config::getInstance()->application->subpath('');

        if (strlen($subPath) > 0 && substr($url, 0, strlen($subPath)) == $subPath)
            $url = substr($url, strlen($subPath));
        $url = preg_replace('/[^A-Za-z0-9\-\/]/', '', $url);
        if (strlen($url) == 0)
            return '\\';
        return strtolower($url);
    }

    /**
     * Gets a not cleaned URL
     * @return string a request url
     */
    public function getRawUrl() {
        return $_SERVER['REQUEST_URI'];
    }
}