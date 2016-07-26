<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\helpers\PathHelper;

class ModelsFactory
{

    /**
     * Loads a test of models from JSON files
     * @param $components
     * @return array
     */
    public static function loadModels($components)
    {
        $models = array();

        foreach ($components as $component) {
            //TODO: throw exception if two keys are equal
            $models[$component->name] = self::loadJSON($component->componentName, $component->dataSet);
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

    /**
     * Some global variables
     * @return array
     */
    public static function loadGlobalModel()
    {
        $global = [
            'css' => '/' . ResourcesFactory::cssFilePath . '?' . time(),
            'js' => '/' . ResourcesFactory::jsFilePath . '?' . time(),
        ];

        return ['GLOBAL' => (object)$global];
    }
}
