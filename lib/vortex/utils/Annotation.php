<?php
/**
 * Project: VortexMVC
 * Author: Rostislav Khanyukov, Illia Ovchynnikov
 * Date: 11-Jun-14
 */

namespace vortex\utils;

/**
 * Class Vortex_Annotation
 * This class implements annotations for classes and their methods
 */
class Annotation {
    const PATTERN = '/\s*\*\s*\@([a-z0-9-_]+)\((.*)\).*/i';

    /* Predefined annotations */
    const REQUEST_MAPPING = 'RequestMapping';
    const REDIRECT = 'Redirect';
    const PERMISSIONS = 'PermissionLevels';


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

    /**
     * Gets parsed annotations for all methods of particular class
     * @param string $className class, where to find methods
     * @param int $reflectFilter a reflection filter of methods {@see \ReflectionMethod}
     * @return array annotation array
     */
    public static function getAllMethodsAnnotations($className, $reflectFilter = \ReflectionMethod::IS_PUBLIC) {
        $classInfo = new \ReflectionClass($className);

        $annotations = array();
        $methods = $classInfo->getMethods($reflectFilter);
        foreach ($methods as $refMethod) {
            $annotations[$refMethod->getShortName()] = self::parseDocComment($refMethod->getDocComment());
        }

        return $annotations;
    }

    /**
     * Gets all annotations for classes and their methods in dir
     * @param string $dir folder to find classes
     * @param string $filter a filter of files for glob()
     * @param string $classNamespace classes namespace
     * @param string $suffix file suffix for filtering and getting basename of file
     * @return array annotation array
     */
    public static function getAllClassFilesAnnotations($dir, $filter = '/*', $classNamespace = '', $suffix = '.php') {
        $annotations = array();

        foreach (glob($dir . $filter . $suffix) as $file) {
            $classShortName = basename($file, $suffix);
            $classFullName = $classNamespace . $classShortName;

            $annotations[$classShortName]['class'] = array(
                'name' => $classFullName,
                'namespace' => $classNamespace,
                'annotation' => self::getClassAnnotation($classFullName)
            );

            $annotations[$classShortName]['methods'] = self::getAllMethodsAnnotations($classFullName);
        }

        return $annotations;
    }

    /**
     * Gets annotations for a method of particular class
     * @param string $className class name
     * @param string $method method name
     * @return array|null an array of annotations (or null, if no doc comment)
     */
    public static function getMethodAnnotations($className, $method) {
        $method = new \ReflectionMethod($className, $method);
        $docComment = $method->getDocComment();
        return self::parseDocComment($docComment);
    }

    /**
     * Parses a doc comment
     * @param string $comment string with annotation
     * @return array|null an array of parsed annotations (or null, if no doc comment)
     */
    private static function parseDocComment($comment) {
        if (empty($comment))
            return null;
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