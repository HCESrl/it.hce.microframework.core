<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\helpers\PathHelper;

class ResourcesFactory
{
    const cssFilePath = 'css/main.css';
    const jsFilePath = 'js/main.js';

    private static $sassFactory;
    private static $jsFactory;

    /**
     * Writes the resource package
     */
    public static function writeResources()
    {
        self::writeCSS();
        self::writeJS();
    }

    /**
     * Writes a compiled CSS on public/css/main.css if not locked
     */
    public static function writeCSS()
    {
        $targetCssPath = PathHelper::getPublicPath(self::cssFilePath);

        if (!PathHelper::isResourceLocked($targetCssPath)) {
            // Write minified CSS to main.css
            self::$sassFactory = new SassFactory();
            self::$sassFactory->collect();
            self::$sassFactory->compile();
            self::$sassFactory->write($targetCssPath);
        }
    }

    /**
     * Writes a compiled JS on public/js/main.js if not locked and the last compile is newer than the last compoents' edit
     */
    public static function writeJS()
    {
        $targetJsPath = PathHelper::getPublicPath(self::jsFilePath);

        if (!PathHelper::isResourceLocked($targetJsPath)
            && PathHelper::getLastEditDate(PathHelper::getComponentsPath(), '/^.+\.js$/i') > filemtime($targetJsPath)
        ) {
            // Write minified JS to main.js
            self::$jsFactory = new JavascriptFactory();
            self::$jsFactory->collectJS();
            self::$jsFactory->write($targetJsPath);
        }
    }
}
