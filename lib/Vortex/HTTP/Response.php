<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 19-May-14
 *
 */

namespace Vortex\HTTP;

/**
 * Class Vortex_Response implements a wrapper of HTTP Response PACKET with additional,
 * extended functionality
 * @package Vortex
 * @subpackage HTTP
 */
class Response {
    /* Status codes */
    const STATUS_CONTINUE_100 = 100;
    const STATUS_SWITCHING_PROTOCOLS = 101;
    const STATUS_PROCESSING = 102;
    const STATUS_OK_200 = 200;
    const STATUS_CREATED_201 = 201;
    const STATUS_ACCEPTED_202 = 202;
    const STATUS_NON_AUTHORITATIVE_203 = 203;
    const STATUS_NO_CONTENT_204 = 204;
    const STATUS_RESET_CONTENT_205 = 205;
    const STATUS_PARTIAL_CONTENT_206 = 206;
    const STATUS_MULTIPLE_CHOICES_300 = 300;
    const STATUS_MOVED_PERMANENTLY_301 = 301;
    const STATUS_FOUND_302 = 302;
    const STATUS_SEE_OTHER_303 = 303;
    const STATUS_NOT_MODIFIED_304 = 304;
    const STATUS_USE_PROXY_305 = 305;
    const STATUS_TEMPORARY_REDIRECT_307 = 307;
    const STATUS_BAD_REQUEST_400 = 400;
    const STATUS_UNAUTHORIZED_401 = 401;
    const STATUS_PAYMENT_REQUIRED_402 = 402;
    const STATUS_FORBIDDEN_403 = 403;
    const STATUS_NOT_FOUND_404 = 404;
    const STATUS_METHOD_NOT_ALLOWED_405 = 405;
    const STATUS_NOT_ACCEPTABLE_406 = 406;
    const STATUS_PROXY_AUTHENTICATION_REQUIRED_407 = 407;
    const STATUS_REQUEST_TIMEOUT_408 = 408;
    const STATUS_CONFLICT_409 = 409;
    const STATUS_GONE_410 = 410;
    const STATUS_LENGTH_REQUIRED_411 = 411;
    const STATUS_PRECONDITION_FAILED_412 = 412;
    const STATUS_REQUEST_ENTITY_TOO_LARGE_413 = 413;
    const STATUS_REQUEST_URI_TOO_LONG_414 = 414;
    const STATUS_UNSUPPORTED_MEDIA_TYPE_415 = 415;
    const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE_416 = 416;
    const STATUS_EXPECTATION_FAILED_417 = 417;
    const STATUS_LOCKED = 423;
    const STATUS_TOO_MANY_REQUESTS = 429;
    const STATUS_INTERNAL_SERVER_ERROR_500 = 500;
    const STATUS_NOT_IMPLEMENTED_501 = 501;
    const STATUS_BAD_GATEWAY_502 = 502;
    const STATUS_SERVICE_UNAVAILABLE_503 = 503;
    const STATUS_GATEWAY_TIMEOUT_504 = 504;
    const STATUS_HTTP_VERSION_NOT_SUPPORTED_505 = 505;
    const STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const STATUS_INSUFFICIENT_STORAGE = 507;
    const STATUS_LOOP_DETECTED = 508;
    const STATUS_BANDWIDTH_LIMIT_EXCEEDED_509 = 509;

    /* Descriptions of status codes */
    protected static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        423 => 'Locked',
        429 => 'Too Many Requests',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded'
    );

    /* HTTP Response Packet */
    protected $version = 'HTTP/1.1';
    protected $statusCode = 200;
    protected $reason;
    protected $headers = array();
    protected $body;

    /**
     * Gets a body of a http packet
     * @return string a body
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Sets a body of a http packet
     * @param string $body a body value
     */
    public function setBody($body) {
        $this->body = (string)$body;
    }

    /**
     * Gets an array of all headers of this packet
     * @return array assoc array of headers
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Adds new header to http response packet
     * @param string $name header name
     * @param string $value header value
     */
    public function setHeader($name, $value) {
        $this->headers[(string)$name] = (string)$value;
    }

    /**
     * Gets a current status code
     * @return int a status code
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Sets a response status code
     * @param int $statusCode a status code
     * @throws ResponseException if status code is wrong (100 ~ 599 supports only)
     */
    public function setStatusCode($statusCode) {
        if ($statusCode >= 600 || $statusCode < 100)
            throw new ResponseException('Unknown status code (100 ~ 599 only)!');
        $this->statusCode = $statusCode;
    }

    /**
     * Gets a version of HTTP from packet's status line
     * @return string a version
     */
    public function getHttpVersion() {
        return $this->version;
    }

    /**
     * Sets a version of HTTP from packet's status line
     * @param string $version (1.0 and 1.1 allowed)
     * @throws ResponseException if version is unsupported
     */
    public function setHttpVersion($version) {
        if ($version != '1.0' || $version != '1.1')
            throw new ResponseException('Supported only 1.0 and 1.1 versions!');
        $this->version = 'HTTP/' . $version;
    }

    /**
     * Prepares headers and sends a packet's body
     */
    public function sendPacket() {
        /***************************
         *          PACKET          *
         ****************************
         * HTTP/1.1 200 OK          *   // Status line
         * ------------------------ *
         * Connection: close        *   // Headers
         * Content-type: text/html  *
         *           ...            *
         * ------------------------ *
         *       MESSAGE BODY       *   // The data
         ***************************/
        if (headers_sent())
            throw new ResponseException('Can\'t start session coz headers have been already started!');

        $statusLine = $this->version . ' ' . $this->statusCode . ' ' . self::$messages[$this->statusCode];
        header($statusLine, true, $this->statusCode);

        foreach ($this->headers as $name => $value)
            header($name . ':' . $value);

        echo $this->body;
    }

    public function redirect($url, $code = Response::STATUS_FOUND_302) {
        $this->setHeader('Location', $url);
        $this->setStatusCode($code);
    }
}