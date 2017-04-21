<?php

namespace bc\rest\codeAnalyzer;

use gossi\swagger\Operation;
use gossi\swagger\Path;
use gossi\swagger\Swagger;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Yaml\Yaml;

class CodeAnalyzer
{

    private $srcPath;

    private $swagger;

    private $namespace;


    /**
     * CodeAnalyzer constructor.
     * @param $srcPath
     * @param $options
     */
    public function __construct($srcPath, $options)
    {
        if(!file_exists($srcPath)){
            throw new \InvalidArgumentException("Source path not exists");
        }
        $this->checkParam($options['swagger'], "Required parameter 'swagger' not set");
        $this->checkParam($options['namespace'], "Required parameter 'namespace' not set");

        $yml                = file_get_contents($options['swagger']);
        $swagger            = Yaml::parse($yml);
        $this->srcPath      = $srcPath;
        $this->swagger      = new Swagger($swagger);
        $this->namespace    = rtrim($options['namespace'], '\\');
    }

    public function run(){
        return $this->getChangedControllers();
    }

    private function checkParam($param, $message) {

        if(!isset($param) || empty($param)) {
            throw new \InvalidArgumentException($message);
        }
    }

    private function getChangedControllers() {

        $controllers = [];
        foreach($this->getPathGroups() as $group => $info) {

            $name       = ucfirst(strtolower($group)) . 'Controller';
            $classPath  = $this->srcPath . str_replace('\\', '/',  $this->namespace) . "/Controllers/" . $name . '.php';
            $methods    = $this->getChangedMethods($classPath);

            if(!empty($methods)){
                $controllers[$name] = $methods;
            }
        }

        return $controllers;
    }


    private function getChangedMethods($classPath) {

        $fullPath       = $this->getAppPath() . trim($classPath, ".");
        $methods        = [];
        $controller     = shell_exec("cat $fullPath");
        $controllerArr  = explode('public', $controller);

        unset($controllerArr[0]);

        foreach ($controllerArr as $item) {
            if($this->isMethodChanged($item)){
                $methods[$this->getMethodName($item)] = $this->getMethodBody($item) ;
            }
        }

        return $methods;
    }

    private function getMethodBody($string){

        $pattern = "/\\{([^\\[\\]]+)\\}\n\n/";

        preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE);
        $body =  trim($matches[1][0]);
//        $body =  preg_replace("/        /", "", $body);
//        $body = preg_replace('# {2,}#', '', $body);
        $body = preg_replace('/[ ]{6,}|[\t]/', '', trim($body));

//        $body = "\t".$body;

//        var_dump($body); die;

        return $body ;
    }


    private function getAppPath(){
        return "/home/id/PhpstormProjects/php-lib/generator";
    }

    private function isMethodChanged($item){

       $pattern = '/TODO Method '.$this->getMethodName($item).' not implemented/';

       if(preg_match($pattern, $item)){
           return false;
       } else {
           return true;
       }
    }


    private function getMethodName($str) {
        $string = explode(" ", $str);
        $key = array_search("function", $string);
        $nameArr = explode("(", $string[$key + 1]);
        return $nameArr[0];
    }


    private function getPathGroups()
    {
        $paths = $this->swagger->getPaths();

        $groups = [];

        /** @var Path $path */
        foreach ($paths as $path) {
            $elements = explode('/', ltrim($path->getPath(), '/'));
            if (count($elements) == 0) {
                $elements[0] = 'default';
            }
            if (!isset($groups[$elements[0]])) $groups[$elements[0]] = [];

            foreach (Swagger::$METHODS as $method) {
                if ($path->hasOperation($method)) {
                    /** @var Operation $operation */
                    $operation = $path->getOperation($method);
                    $groups[$elements[0]][] = [
                        'method' => $method,
                        'operation' => $operation,
                        'path' => $path,
                    ];
                }
            }
        }

        return $groups;
    }
}