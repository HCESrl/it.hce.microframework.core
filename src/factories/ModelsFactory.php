<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\helpers\PathHelper;

class ModelsFactory
{

    /**
     * Loads a test of models from JSON files
     * @param $components
     * @return array
     * @throws MicroFrameworkException
     */
    public static function loadModels($components)
    {
        $models = array();

        foreach ($components as $component) {
            $models[$component->name] = self::loadJSON($component->componentName, $component->dataSet);
        }

        if (!self::checkModel($components)) {
            throw new MicroFrameworkException('Your configuration has components\' declarations duplicates');
        }

        return $models;
    }

    /**
     * @param $componentName
     * @param $dataSet
     * @return object
     */
    public static function loadJSON($componentName, $dataSet)
    {
        return (object)json_decode(file_get_contents(PathHelper::getComponentsPath($componentName . '/datasets/' . $dataSet . '.json')), true);
    }

    private static function checkModel($model)
    {
        // create a 'name' array
        $map = array_map(create_function('$o', 'return $o->name;'), $model);

        // check if we have duplicates
        return count($map) === count(array_unique($map));
    }

    /**
     * Some global variables
     * @return array
     */
    public static function loadGlobalModel()
    {
        $global = [
            'css' => '/' . ResourcesFactory::cssFilePath . '?' . time(),
            'rtlCss' => '/' . ResourcesFactory::rtlFilePath . '?' . time(),
            'js' => '/' . ResourcesFactory::jsFilePath . '?' . time(),
        ];

        return ['GLOBAL' => (object)$global];
    }
}
