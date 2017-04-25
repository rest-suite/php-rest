<?php

namespace bc\rest\codeAnalyzer;


use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\swagger\Operation;
use gossi\swagger\Path;
use gossi\swagger\Swagger;
use Symfony\Component\Yaml\Yaml;

class CodeAnalyzer
{
    /** @var string $srcPath  */
    private $srcPath;

    /** @var Swagger $swagger */
    private $swagger;

    /** @var string $namespace */
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

    /**
     * @return array
     */
    public function run(){
        return $this->getChangedControllers();
    }

    /**
     * @param $param
     * @param $message
     */
    private function checkParam($param, $message) {

        if(!isset($param) || empty($param)) {
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * @return array
     */
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

    /**
     * @param $classPath
     * @return array
     */
    private function getChangedMethods($classPath) {

        $fullPath       = $this->getAppPath() . trim($classPath, ".");
        $controller     = PhpClass::fromFile($fullPath);
        $methods        = [];

        foreach ($controller->getMethodNames() as $methodName) {
            $method = $controller->getMethod($methodName);
            if($this->isMethodChanged($method)) {
                $methods[$method->getName()] = $method->getBody();
            }
        }

        return $methods;
    }

    /**
     * @return string
     */
    private function getAppPath(){
        return __DIR__ . '/../../../..';
    }


    /**
     * @param PhpMethod $method
     * @return bool
     */
    private function isMethodChanged($method){

       $pattern = '/TODO Method ' . $method->getName() . ' not implemented/';

       if(preg_match($pattern, $method->getBody())){
           return false;
       } else {
           return true;
       }
    }


    /**
     * @return array
     */
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