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
            <table class="vortex-logger ' . $level . '">
                <tr>
                    <td align="left">Message level: <span class="level ' . $level . '">' . self::$level . '</span></td>
                </tr>
                <tr>
                    <td class="message ' . $level . '">
                        <pre>' . $string . '</pre>
                    </td>
            </table>
        ';
    }

    public static function error($txt) {
        self::messageBody($txt, 'error');
    }

    public static function warning($txt) {
        if (self::$level >= self::WARNING)
            self::messageBody($txt, 'warning');
    }

    public static function info($txt) {
        if (self::$level >= self::INFO)
            self::messageBody($txt, 'info');
    }

    public static function debug($txt) {
        if (self::$level == self::DEBUG)
            self::messageBody($txt, 'debug');
    }
} 