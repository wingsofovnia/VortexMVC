<?php
namespace Vortex;
/**
 * Project: VortexMVC
 * Author: Rostislav Khanyukov
 * Date: 11-Jun-14
 * Time: 1:03
 *
 * @package Vortex
 */

/**
 * Class Vortex_Annotation
 * This class implements annotations
 */
class Annotation {

	private static $pattern = '/\s*\*\s*\@([a-z0-9-_]+)\((.*)\).*/i';

	/**
     * Gets data from doc-comment
     * @param string $className className
	 * @return array parsed data from doc-comment
     */
	public static function getAnnotations($className) {
		$classInfo = new \ReflectionClass($className);
		$docComment = $classInfo->getDocComment();
        $data = array();
		if (preg_match_all(self::$pattern, $docComment, $matches)) {
			foreach ($matches[1] as $i => $key) {
				$values = explode(',', trim($matches[2][$i]));
				foreach ($values as $v) {
		    		$data[$key][] = trim($v, "' ");
				}
		    }
		}
		return $data;
    }

    /**
     * Gets request mapping from doc-comment
     * @param string $className className
     * @return array parsed request mapping from doc-comment
     */
    public static function getRequestMapping($className) {
        $annotations = self::getAnnotations($className);
        return $annotations['RequestMapping'];
    }

    /**
     * Gets allowed groups from doc-comment
     * @param string $className className
     * @return array parsed allowed groups from doc-comment
     */
    public static function getAllowedGroups($className) {
        $annotations = self::getAnnotations($className);
        return $annotations['Allow'];
    }

    /**
     * Gets denied groups from doc-comment
     * @param string $className className
     * @return array parsed denied groups from doc-comment
     */
    public static function getDeniedGroups($className) {
        $annotations = self::getAnnotations($className);
        return $annotations['Deny'];
    }

    /**
     * Gets redirect controller from doc-comment
     * @param string $className className
     * @return string parsed redirect controller from doc-comment
     */
    public static function getRedirectController($className) {
        $annotations = self::getAnnotations($className);
        return $annotations['Redirect'][0];
    }

    /**
     * Gets redirect action from doc-comment
     * @param string $className className
     * @return string parsed redirect controller from doc-comment
     */
    public static function getRedirectAction($className) {
        $annotations = self::getAnnotations($className);
        return $annotations['Redirect'][1];
    }

}

