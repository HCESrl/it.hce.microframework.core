<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\exceptions\MicroFrameworkException;
use it\hce\microframework\core\helpers\PathHelper;
use MatthiasMullie\Minify\JS;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class JavascriptFactory
{
    const staticJS = 'javascripts.json';

    private $minifier;

    /**
     * JavascriptFactory constructor.
     */
    public function __construct()
    {
        $this->minifier = new JS();
    }

    /**
     * Collects the whole JS library
     */
    public function collectJS()
    {
        // Read manual JS includes from a JSON file
        $this->getStaticJS();

        // Read components' JS
        $this->getComponentsJS();
    }

    private function getStaticJSFileList() {
        // get the file and decode it
        $file = file_get_contents(PathHelper::getConfigPath(self::staticJS));
        $files = json_decode($file);

        // complete the path
        foreach ($files as $key => $value) {
            $files[$key] = PathHelper::getBasePath() . $value;
        }

        return $files;
    }

    public function getStaticJSLastEditDate() {
        $files = $this->getStaticJSFileList();
        $time = 0;

        foreach($files as $key => $value){
            $modTime = filemtime($value);
            if($modTime > $time){
                $time = $modTime;
            }
        }
        return $time;
    }

    private function getStaticJS()
    {

        $files = $this->getStaticJSFileList();
        // collect the static libraries
        $this->collectJSFromFiles($files);
    }

    private function collectJSFromFiles($files)
    {
        foreach ($files as $file) {
            $this->collectJSFromFile($file);
        }
    }

    private function collectJSFromFile($file)
    {
        $jsResult = "\r\n/* INCLUDE " . $file . " */ \r\n" . file_get_contents($file);
        $this->minifier->add($jsResult);
    }

    private function getFileListFromDirectory($directory){
        $directory = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($directory);
        $result = new RegexIterator($iterator, '/^.+\.js$/i', RecursiveRegexIterator::GET_MATCH);

        return $result;
    }

    private function getComponentsJS()
    {
        $this->collectJSFromDirectory(PathHelper::getComponentsPath());
    }

    private function collectJSFromDirectory($directory)
    {
        //look for all script.js in components' folder recursively
        $result = $this->getFileListFromDirectory($directory);

        foreach ($result as $key => $value) {
            // collect the JS source
            $this->collectJSFromFile($key);
        }
    }

    /**
     * Writes the output in a file
     * @param $file string path of the file
     * @throws MicroFrameworkException the file is not writable
     */
    public function write($file)
    {
        if (is_writable(dirname($file))) {
            file_put_contents($file, $this->output());
        } else {
            throw new MicroFrameworkException($file . ' is not writable');
        }
    }

    /**
     * Get the minifier output
     * @return string min output
     */
    public function output()
    {
        return $this->minifier->minify();
    }
}
