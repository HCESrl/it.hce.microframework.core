<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\helpers\PathHelper;
use Jenssegers\Blade\Blade;

class TemplateFactory
{
    static $blade;

    /**
     * Loads a Blade template
     * @param string $templateName
     * @param $models
     * @return string
     */
    public static function loadTemplate($templateName = 'templates.homepage', $models = [])
    {
        // load Blade engine
        self::$blade = new Blade(PathHelper::getBasePath(), PathHelper::getCachePath());

        return self::$blade->render($templateName, $models);
    }
}
