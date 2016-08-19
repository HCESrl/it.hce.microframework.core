<?php

namespace it\hce\microframework\core\helpers;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class PathHelper
{

    /**
     * @param string $file inside the public path
     * @return string complete path
     */
    public static function getPublicPath($file = '')
    {
        return self::getBasePath() . 'public/' . $file;
    }

    /**
     * @return string project's base path
     */
    public static function getBasePath()
    {
        return realpath(dirname(__FILE__)) . '/../../../../../';
    }

    /**
     * @param string $file inside the templates path
     * @return string complete path
     */
    public static function getTemplatesPath($file = '')
    {
        return self::getBasePath() . 'templates/' . $file;
    }

    /**
     * @param string $file inside the components path
     * @return string complete path
     */
    public static function getComponentsPath($file = '')
    {
        return self::getBasePath() . 'components/' . $file;
    }

    /**
     * @param string $file inside the config path
     * @return string complete path
     */
    public static function getConfigPath($file = '')
    {
        return self::getBasePath() . 'config/' . $file;
    }

    /**
     * @param string $file inside the resources path
     * @return string complete path
     */
    public static function getResourcesPath($file = '')
    {
        return self::getBasePath() . 'resources/' . $file;
    }

    /**
     * @return string complete cache path
     */
    public static function getCachePath()
    {
        return self::getBasePath() . 'cache/';
    }

    /**
     * Checks if a resource exists and it's locked
     * @param $resource
     * @return bool
     */
    public static function isResourceLocked($resource)
    {
        return file_exists($resource . ".lock") && file_exists($resource);
    }

    /**
     * @param $resource
     * @param $filter
     * @return bool|int
     */
    public static function getLastEditDate($resource, $filter)
    {
        $lastEditDate = 0;

        $resource = new RecursiveDirectoryIterator($resource);
        $iterator = new RecursiveIteratorIterator($resource);
        $result = new RegexIterator($iterator, $filter, RecursiveRegexIterator::GET_MATCH);

        foreach ($result as $key => $value) {
            $thisEditTime = filemtime($key);
            if ( $thisEditTime > $lastEditDate) {
                $lastEditDate = $thisEditTime;
            }
        }

        return $lastEditDate;
    }
}

