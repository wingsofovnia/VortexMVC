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
	
	private $pattern = '/\s*\*\s*\@([a-z0-9-_]+)\((.*)\).*/i';
	
	/**
     * Gets data from doc-comment
     * @param string $classname classname
	 * @return array parsed data from doc-comment
     */
	public function getAnnotations($classname) {
		$classInfo = new \ReflectionClass($classname);
		$docComment = $classInfo->getDocComment();
        $data = array();
		if (preg_match_all($this->pattern, $docComment, $matches)) {
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
