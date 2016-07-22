<?php

namespace it\hce\microframework\core;


abstract class AComponent
{
    public $config = [];

    public function __construct($basePath, $name, $data)
    {
        $this->name = $name;
        $this->basePath = $basePath;
        $dataset = $data["dataset"];
        $this->dataSet =  $this->loadDataSet($dataset);
        $this->html = $this->loadHtml();
        $this->css = $this->loadCss();
        $this->config = $data;
        $this->hydrate();
    }

    public function getConfigValue($name)
    {
        if (array_key_exists($name, $this->config))
        {
            return $this->config[$name];
        }

        return null;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param $key
     * @param $data
     * @return string
     * internal function to preprocess data
     */
    public function preprocessData($key, $data)
    {
        return $data;
    }

    public function hydrate()
    {
        foreach ($this->dataSet as $key => $data)
        {
            if(! is_string($data)){
                continue;
            }
            $data = $this->preprocessData($key, $data);

            // first try optional fields [[<p>{{{$key}}}</p>]]
            // you can have multiple optional fields in a sequence, if any of the two is empty they are both removed
            $regexp = '/\[\[(.+\{\{\{\$' . $key . '\}\}\}.+)\]\]/';
            while (preg_match($regexp, $this->html, $matches))
            {
                $found = $matches[0];
                if ($data == "" || $data == false)
                {
                    $this->html = str_replace($found, "", $this->html);
                } else {
                    $content = $matches[1];
                    $content = str_replace('{{{$' . $key . '}}}', $data, $content);
                    $this->html = str_replace($found, $content, $this->html);
                }
            }

            // then compulsory fields
            $this->html = str_replace('{{{$' . $key . '}}}', $data, $this->html);
        }

        $this->postprocessData();
    }

    public function postprocessData()
    {

    }

    private function loadDataSet($name = 'default')
    {
        if(!$name){
            $name = 'default';
        }
        return json_decode(file_get_contents(MicroFramework::getComponentsPath() . $this->name . '/datasets/' . $name . '.json'), true);
    }

    private function loadCss($css = 'head')
    {
        $path = MicroFramework::getComponentsPath() . $this->name . '/' . $css . '.css';
        if (file_exists($path))
        {
            return file_get_contents($path);
        }

        return '';
    }

    private function loadHtml($html = 'template')
    {
        $template =  file_get_contents(MicroFramework::getComponentsPath() . $this->name . '/' . $html . '.html');

        // look for subtemplates
        $pattern = "/(\[\[\[\\$[a-zA-Z0-9-_]+\]\]\])/";

        $search =  preg_match_all($pattern, $template, $matches);
        if($search === 1){
            foreach($matches[1] as $k){
                $variable = str_replace(array("[[[$", "]]]"), "", $k);
                if(array_key_exists($variable, $this->dataSet)){
                    $subtemplate_path = MicroFramework::getComponentsPath() . $this->name . '/_' . $this->dataSet[$variable]["templateName"] . '.html';
                    if(file_exists($subtemplate_path)){
                        $subtemplate =  file_get_contents($subtemplate_path);
                        $template = str_replace($k, $subtemplate, $template);
                    } else { // file cannot be loaded
                        $template = str_replace($k, "", $template);
                    }

                } else {// subtemplate not found, remove placeholder
                    $template =  str_replace($k, "", $template);
                }
            }
        }

        return $template;
    }
}