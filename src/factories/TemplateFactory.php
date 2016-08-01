<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\helpers\PathHelper;
use Jenssegers\Blade\Blade;

class TemplateFactory
{
    private static $blade;
    private static $config;

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

        // load blade plugins
        self::loadPlugins();

        return self::$blade->render($templateName, $models);
    }

    private static function loadPlugins()
    {
        self::$config = file_get_contents(PathHelper::getConfigPath('environment.json'));

        // responsive images
        self::responsiveImagesBlade();
    }

    private static function responsiveImagesBlade()
    {
        self::$blade->compiler()->directive('responsiveImage', function ($value) {
            return '<?php
                //explode
                $value = ' . $value . ';
                $image = $value[\'image\'];
                $componentName = $value[\'component\'];
                $attributes = isset($value[\'attributes\']) ? $value[\'attributes\'] : \'\';
                $config = json_decode(\'' . self::$config . '\')->scalableImages->{$componentName};
    
                // get vars
                $srcSet = $config->resolutions;
    
                // src attribute
                $outSrc = \'src="../images/components/\' . $srcSet[0] . \'/\' . $componentName . \'/\' . $image . \'"\';
    
                // srcset attribute
                $outSrcSet = \'srcset="\';
                foreach ($srcSet as $width) {
                    $outSrcSet .= \'../images/components/\' . $width . \'/\' . $componentName . \'/\' . $image . \' \' . $width . \'w, \';
                }
                $outSrcSet = substr($outSrcSet, 0, -2) . \'"\';
    
                //sizes attribute
                $attributes .= $config->sizes !== \'\' ? \'sizes="\' . $config->sizes . \'"\' : $config->sizes;
    
                // glue all
                $output = \'<img \' . trim($outSrc) . \' \' . trim($outSrcSet) . \' \' . trim($attributes) . \' />\';
                
                echo $output; ?>';
        });
    }
}
