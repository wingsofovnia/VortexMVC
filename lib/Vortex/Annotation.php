<?php
namespace Vortex;
/**
 * Project: VortexMVC
 * Author: Rostislav Khanyukov, Illia Ovchynnikov
 * Date: 11-Jun-14
 *
 * @package Vortex
 */

/**
 * Class Vortex_Annotation
 * This class implements annotations
 */
class Annotation {
	const PATTERN = '/\s*\*\s*\@([a-z0-9-_]+)\((.*)\).*/i';

    const REQUEST_MAPPING = 'RequestMapping';
    const REDIRECT = 'Redirect';
    const ALLOW = 'Allow';
    const DENY = 'Deny';


	/**
     * Gets data from doc-comment
     * @param string $className className
	 * @return array parsed data from doc-comment
     */
	public static function getClassAnnotation($className) {
		$classInfo = new \ReflectionClass($className);
		$docComment = $classInfo->getDocComment();
		return self::parseDocComment($docComment);
    }

    public static function getAllMethodsAnnotations($className) {
        $classInfo = new \ReflectionClass($className);

        $annotations = array();
        $methods = $classInfo->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $refMethod) {
            $annotations[$refMethod->getShortName()] = self::parseDocComment($refMethod->getDocComment());
        }

        return $annotations;
    }

    public static function getMethodAnnotations($className, $method) {
        $method = new \ReflectionMethod($className, $method);
        $docComment = $method->getDocComment();
        return self::parseDocComment($docComment);
    }

    private static function parseDocComment($comment) {
        $data = array();
        if (preg_match_all(self::PATTERN, $comment, $matches)) {
            foreach ($matches[1] as $i => $key) {
                $values = explode(',', trim($matches[2][$i]));
                foreach ($values as $v) {
                    $data[$key][] = trim($v, "' ");
                }
            }
        }
        return $data;
    }

}

