<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 */

namespace Vortex\HTTP;
use Vortex\MVC\Router;

/**
 * Class Vortex_Request implements a wrapper of HTTP Request with additional,
 * extended functionality
 * @package Vortex
 * @subpackage HTTP
 */
class Request {
    private $get;
    private $post;
    private $cookies;
    private $params;
    private $method;

    private $router;

    /**
     * Init constructor
     */
    public function __construct() {
        $_POST = array_map('trim', $_POST);
        $this->post = $_POST;

        $_GET = array_map('trim', $_GET);
        $this->get = $_GET;

        $_COOKIE = array_map('trim', $_COOKIE);
        $this->cookies = $_COOKIE;

        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->router = new Router($this);
        $this->router->parse();
        $this->params = new \ArrayObject($this->router->getParams());
    }

    /**
     * Adds an array of key-value pairs to storage
     * @param $params
     */
    public function addParams($params) {
        foreach ($params as $k => $v)
            $this->addParam($k, $v);
    }

    /**
     * Adds value to internal storage
     * @param string $key a key
     * @param mixed $value a value
     */
    public function addParam($key, $value) {
        $this->params->$key = $value;
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
     * Gets a PARAM value by key
     * @param string $key a key
     * @param null|mixed $default value that will be settled, if no PARAM found
     * @return string a PARAM value
     */
    public function getParam($key, $default = null) {
        return !is_null($this->params->$key) ? $this->params->$key : $default;
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
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
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
     * Gets a not cleaned URL
     * @return string a request url
     */
    public function getRawUrl() {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Gets cleaned REQUEST_URI
     * @return string a request url
     */
    public function getRequestUrl() {
        return $this->router->getUrl();
    }

    /**
     * Gets a name of controller parsed from url
     * @return string controller's name
     */
    public function getController() {
        return $this->router->getController();
    }

    /**
     * Gets a name of action parsed from url
     * @return string action's name
     */
    public function getAction() {
        return $this->router->getAction();
    }

    /**
     * Returns an array of permission level for this action
     * @return array array of int levels
     */
    public function getPermissions() {
        return $this->router->getPermissions();
    }
}