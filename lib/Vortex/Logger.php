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
        $string = self::$desc[$level] . "\n" . $string;
        echo '
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                       <pre>' . $string . '</pre>
                    </div>
                    </div>
                </div>
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