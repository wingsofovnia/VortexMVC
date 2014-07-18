<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 14-Jun-14
 * Time: 22:37
 */

namespace Vortex\Utils;

abstract class Service {
    public static function execTime($function, $echo = false) {
        if (!is_callable($function))
            throw new \InvalidArgumentException('U should place a callable funct to calc it\'s exec time!');
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $starttime = $mtime;

        $function();

        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        $totaltime = ($endtime - $starttime);

        if ($echo !== true)
            return $totaltime;
        echo "Execution time: " . $totaltime . " seconds";
    }

    public static function fileExists($fileName, $caseSensitive = true) {
        if (file_exists($fileName)) {
            return $fileName;
        }

        if ($caseSensitive)
            return false;

        $directoryName = dirname($fileName);
        $fileArray = glob($directoryName . '/*', GLOB_NOSORT);
        $fileNameLowerCase = strtolower($fileName);
        foreach ($fileArray as $file) {
            if (strtolower($file) == $fileNameLowerCase) {
                return $file;
            }
        }
        return false;
    }
} 