<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 15-Jun-14
 * Time: 00:10
 */

namespace Vortex\Utils;

abstract class Numeric {
    public static function parseFloat($string) {
        $string = trim($string);
        if (strpos($string, ',') !== false)
            $string = str_replace(',', '.', $string);
        return floatval($string);
    }
} 