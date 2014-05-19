<?php

/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 18:02
 */
class Vortex_Request {
    private $get;
    private $post;
    private $cookies;
    private $params;
    private $method;

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

        $this->params = array();
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function addParam($key, $value) {
        $this->param[$key] = $value;
        return $this;
    }

    public function addParams($params) {
        foreach ($params as $k => $v) $this->addParam($k, $v);
        return $this;
    }

    public function getPost($key, $default = null) {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    public function getGet($key, $default = null) {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    public function getCookie($key, $default = null) {
        return isset($this->cookies[$key]) ? $this->cookies[$key] : $default;
    }

    public function getParam($key, $default = null) {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    public function getMethod() {
        return $this->method;
    }

    public function isPost() {
        return 'POST' == $this->getMethod();
    }

    public function isGet() {
        return 'GET' == $this->getMethod();
    }

    public function isXMLHttpRequest() {
        return 'XMLHttpRequest' == $this->getHeader('X_REQUESTED_WITH');
    }

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
        return false;
    }

} 