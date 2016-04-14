<?php

namespace it\hce\microframework\core\factories;


use it\hce\microframework\core\MicroFramework;
use it\hce\microframework\core\exceptions\ResourceWriteException;
use MatthiasMullie\Minify;
use MatthiasMullie\Minify\JS;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class JavascriptFactory
{
    private $minifier;
    protected $latestModifiedDate = 0;

    public function __construct()
    {
        include_once(MicroFramework::getBasePath() . 'vendor/matthiasmullie/minify/src/Minify.php');
        include_once(MicroFramework::getBasePath() . 'vendor/matthiasmullie/minify/src/JS.php');
        $this->minifier = new JS();
    }

    /**
     * @return string min output
     */
    public function output()
    {
        return $this->minifier->minify();
    }

    /** 
     * Writes the output in a file
     * @param $file string path of the file
     * @throws ResourceWriteException the file is not writable
     */
    public function write($file)
    {
        $timeOfLatestOutput = filemtime($file);
        if($timeOfLatestOutput < $this->latestModifiedDate) { // latest output was earlier than latest modified time, republish
            if (is_writable(dirname($file))) {
                file_put_contents($file, $this->output());
            } else {
                throw new ResourceWriteException($file . ' not writable');
            }
        } else {
            // do nothing
        }
    }

    public function collectJS()
    {
        // Read manual JS includes from a JSON file
        $this->getStaticLibs();

        // Read components' JS
        $this->getComponentsLibs();
    }

    private function getStaticLibs()
    {
        $file = file_get_contents(MicroFramework::getConfigPath('javascripts.json'));
        $files = json_decode($file);

        foreach ($files as $key => $value) {
            $files[$key] = MicroFramework::getBasePath() . $value;
        }

        $this->collectJSFromFiles($files);
    }

    private function getComponentsLibs()
    {
        $this->collectJSFromDirectory(MicroFramework::getComponentsPath());
    }

    /**
     * @param $directory
     */
    private function collectJSFromDirectory($directory)
    {
        $Directory = new RecursiveDirectoryIterator($directory);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $Regex = new RegexIterator($Iterator, '/^.+\.js$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($Regex as $k => $v) {
            $this->collectJSFromFile($k);
        }
    }

    /**
     * @param $files
     */
    private function collectJSFromFiles($files)
    {
        foreach ($files as $file) {
            $this->collectJSFromFile($file);
        }
    }

    /**
     * @param $file
     * @param $base
     */
    private function collectJSFromFile($file, $base = false)
    {
        // get date of last modification of file
        $fileDate = filemtime($file);
        if($fileDate > $this->latestModifiedDate){
            $this->latestModifiedDate = $fileDate;
        }

        $jsResult = '';
        $jsResult .= "\r\n/* INCLUDE $file */ \r\n";
        $jsResult .= file_get_contents($file);

        $this->minifier->add($jsResult);
    }
}