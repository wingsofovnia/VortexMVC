<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 * Time: 23:16
 */

class Vortex_Logger {
    const ERROR = 0;
    const WARNING = 1;
    const INFO = 2;
    const DEBUG = 3;
    private static $desc = array(0 => 'ERROR', 1 => 'WARNING', 2 => 'INFO', 3 => 'DEBUG');

    private static $level;

    public static function level($lvl) {
            if ($lvl == self::ERROR ||
                    $lvl == self::WARNING ||
                        $lvl == self::INFO ||
                            $lvl == self::DEBUG) {
                self::$level = $lvl;
            }
    }

    private static function messageBody($body, $level) {
        if (is_array($body))
            $string = print_r($body, true);
        else if (is_string($body))
            $string = $body;
        else {
            ob_start();
            var_dump($body);
            $string = ob_get_clean();
        }
        echo '
            <div style="margin:5px;padding:5px;border:1px solid #D5D5D5;border-radius:3px;background:#F6F6F6;">
                <div style="float:left">
                    <small>Message level:</small> <strong>' . self::$desc[$level] . '</strong>
                </div>
                <div style="float:right; margin-bottom:5px">
                    <small>Time:</small> <strong>' . date("H:i:s") . '</strong>
                </div>
                <hr style="margin:5px 0; clear:both;"/>
                <pre style="margin: 5px 0">' . $string . '</pre>
            </div>
        ';
    }

    public static function error($txt) {
        self::messageBody($txt, self::ERROR);
    }

    public static function warning($txt) {
        if (self::$level >= self::WARNING)
            self::messageBody($txt, self::WARNING);
    }

    public static function info($txt) {
        if (self::$level >= self::INFO)
            self::messageBody($txt, self::INFO);
    }

    public static function debug($txt) {
        if (self::$level == self::DEBUG)
            self::messageBody($txt, self::DEBUG);
    }
} 