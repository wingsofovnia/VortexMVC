<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 19-May-14
 */

/**
 * Class Vortex_Logger
 * A simple logger implementation
 */
class Vortex_Logger {
    const EXCEPTION = -1;
    const ERROR = 0;
    const WARNING = 1;
    const INFO = 2;
    const DEBUG = 3;
    private static $desc = array(self::EXCEPTION    => 'EXCEPTION',
                                 self::ERROR        => 'ERROR',
                                 self::WARNING      => 'WARNING',
                                 self::INFO         => 'INFO',
                                 self::DEBUG        => 'DEBUG');
    private static $colors = array(self::EXCEPTION    => '#BC7864',
                                   self::ERROR        => '#FB5F5F',
                                   self::WARNING      => '#F3FF61',
                                   self::INFO         => '#FFECC6',
                                   self::DEBUG        => '#F6F6F6');

    private static $level;

    /**
     * Sets a logging level
     * @param int $lvl level (0 ~ 3) from Vortex_Logger::ERROR to Vortex_Logger::DEBUG
     */
    public static function level($lvl) {
        if (isset(self::$desc[$lvl]))
            self::$level = $lvl;
    }

    /**
     * Prints the logger message
     * @param string $body message to print
     * @param int $level a logger level
     */
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
        $color = self::$colors[$level];
        echo '
            <div style="margin:5px;padding:5px;border:1px solid #D5D5D5;border-radius:3px;background:' . $color . ';">
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

    /**
     * Prints message as an error
     * @param string $txt message
     */
    public static function error($txt) {
        self::messageBody($txt, self::ERROR);
    }

    public static function exception($txt) {
        self::messageBody($txt, self::EXCEPTION);
    }

    /**
     * Prints message as a warning
     * @param string $txt message
     */
    public static function warning($txt) {
        if (self::$level >= self::WARNING)
            self::messageBody($txt, self::WARNING);
    }

    /**
     * Prints message as info text
     * @param string $txt message
     */
    public static function info($txt) {
        if (self::$level >= self::INFO)
            self::messageBody($txt, self::INFO);
    }

    /**
     * Prints message as debug message
     * @param string $txt message
     */
    public static function debug($txt) {
        if (self::$level == self::DEBUG)
            self::messageBody($txt, self::DEBUG);
    }
} 