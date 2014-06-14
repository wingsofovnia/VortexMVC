<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 15-Jun-14
 * Time: 00:08
 */

namespace Vortex\Utils;


abstract class Text {
    public static function inflect($name, $p = NULL) {
        $url = 'http://export.yandex.ru/inflect.xml?name=' . urlencode($name);
        $result = file_get_contents($url);
        $cases = array();
        preg_match_all('#\<inflection\s+case\=\"([0-9]+)\"\>(.*?)\<\/inflection\>#si', $result, $m);
        if (count($m[0])) {
            foreach ($m[1] as $i => &$id) {
                $cases[(int)$id] = $m[2][$i];
            }
            unset ($id);
        } else {
            return NULL;
        }
        if (count($cases) > 1) {
            if (isset($p)) {
                return $cases[$p];
            } else {
                return $cases;
            }
        } else {
            return false;
        }
    }

    public static function translit($str) {
        $tr = array("Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I", "і" => "i", "ґ" => "g", "ё" => "yo",
            "№" => "#", "є" => "e", "ї" => "yi", "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
            "Е" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I", "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M",
            "Н" => "N", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F",
            "Х" => "H", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH", "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
            "е" => "e", "ж" => "zh", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m",
            "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f",
            "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "'", "ы" => "yi", "ь" => "",
            "э" => "e", "ю" => "yu", "я" => "ya");
        return strtr($str, $tr);
    }

    public static function permalink($str) {
        $str = self::translit($str);
        $str = strtolower($str);
        $str = str_replace("&", "and", html_entity_decode($str));
        return trim(preg_replace('/[^\p{L}\p{N}]/u', '-', strtolower($str)), '-');
    }
} 