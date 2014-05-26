<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:02
 */

/**
 * Class Vortex_Request
 * This class implements a wrapper of HTTP Request with addition
 * extended functionality
 */
class Vortex_Request {
    private $get;
    private $post;
    private $cookies;
    private $params;
    private $method;

    /**
     * Init constructor
     */
    public function __construct() {
        $_POST = array_map('trim', $_POST);
        $_POST = array_filter($_POST);
        $this->post = $_POST;

        $_GET = array_map('trim', $_GET);
        $_GET = array_filter($_GET);
        $this->get = $_GET;

        $_COOKIE = array_map('trim', $_COOKIE);
        $_COOKIE = array_filter($_COOKIE);
        $this->cookies = $_COOKIE;

        $this->params = new Vortex_Registry();
        $this->method = $_SERVER['REQUEST_METHOD'];
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
     * Adds an array of key-value pairs to storage
     * @param $params
     */
    public function addParams($params) {
        foreach ($params as $k => $v)
            $this->addParam($k, $v);
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
        return !is_null($this->params->$key) ? $this->params[$key] : $default;
    }

    /**
     * Gets a name of a method of a request from it's headers
     * @return string METHOD name
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Checks if request method is POST
     * @return bool true, if POST, else - false
     */
    public function isPost() {
        return 'POST' == $this->getMethod();
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
     * @throws Vortex_Exception_IllegalArgument if $header name is empty
     */
    public function getHeader($header) {
        if (empty($header))
            throw new Vortex_Exception_IllegalArgument('An HTTP header name is required');
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

} 