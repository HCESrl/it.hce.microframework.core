<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\helpers\PathHelper;

class ResourcesFactory
{
    const cssFilePath = 'css/main.css';
    const rtlFilePath = 'css/main.rtl.css';
    const jsFilePath = 'js/main.js';

    private static $sassFactory;
    private static $jsFactory;

    /**
     * Writes the resource package
     * @param bool $rtl
     */
    public static function writeResources($rtl = false)
    {
        self::writeCSS($rtl);
        self::writeJS();
    }

    /**
     * Writes a compiled CSS on public/css/main.css if not locked
     * @param bool $rtl
     */
    public static function writeCSS($rtl = false)
    {
        $targetCssPath = PathHelper::getPublicPath($rtl ? self::rtlFilePath : self::cssFilePath);

        if (!PathHelper::isResourceLocked($targetCssPath)) {
            // Write minified CSS to main.css
            self::$sassFactory = new SassFactory($rtl);
            self::$sassFactory->collect();
            self::$sassFactory->compile();
            self::$sassFactory->write($targetCssPath);
        }
    }

    /**
     * Writes a compiled JS on public/js/main.js if not locked and the last compile is newer than the last components' edit
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