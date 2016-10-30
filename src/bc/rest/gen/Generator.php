<?php

namespace bc\rest\gen;

use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\ParamTag;
use gossi\swagger\Operation;
use gossi\swagger\Path;
use gossi\swagger\Swagger;
use Symfony\Component\Yaml\Yaml;

class Generator {

    /**
     * @var Swagger
     */
    private $swagger;
    /**
     * @var string
     */
    private $namespace;

    /**
     * Generator constructor.
     *
     * @param string $swaggerFile
     * @param string $namespace
     */
    public function __construct($swaggerFile, $namespace) {
        $yml = file_get_contents($swaggerFile);
        $swagger = Yaml::parse($yml);
        $this->swagger = new Swagger($swagger);
        $this->namespace = $namespace;
    }

    public function getPaths() {
        $paths = $this->swagger->getPaths();

        $groups = [];

        /** @var Path $path */
        foreach($paths as $path) {
            $elements = explode('/', ltrim($path->getPath(), '/'));
            if(count($elements) > 0) {
                if(!isset($groups[$elements[0]])) $groups[$elements[0]] = [];

                foreach(Swagger::$METHODS as $method) {
                    if($path->hasOperation($method)) {
                        /** @var Operation $operation */
                        $operation = $path->getOperation($method);
                        $groups[$elements[0]][] = [
                            'method'    => $method,
                            'operation' => $operation,
                            'path'      => $path,
                        ];
                    }
                }
            }
        }

        /** @var PhpClass[] $controllers */
        $controllers = [];
        foreach($groups as $group => $info) {
            $name = ucfirst(strtolower($group)).'Controller';
            if(!isset($controllers[$name])) {
                $controllers[$name] = new PhpClass($name);
                $controllers[$name]
                    ->setNamespace(rtrim($this->namespace, '\\').'\\controllers')
                    ->setUseStatements(
                        [
                            'Slim\\App',
                            'Psr\\Http\\Message\\ServerRequestInterface',
                            'Psr\\Http\\Message\\ResponseInterface'
                        ])
                    ->setProperties(
                        [
                            PhpProperty::create('app')->setType('App')
                        ])
                    ->setMethod(
                        PhpMethod::create('__construct')
                                 ->addParameter(
                                     PhpParameter::create('app')->setType('App'))
                                 ->setBody('$this->app = $app;'));
            }

            foreach($info as $item) {
                /** @var Operation $op */
                $op = $item['operation'];
                /** @var Path $path */
                //$path = $item['path'];
                if(!empty($op->getOperationId())) {
                    $method = PhpMethod::create($op->getOperationId());
                    $method->setDocblock(
                        Docblock::create()
                                ->appendTag(ParamTag::create()->setVariable('request')->setType('ServerRequestInterface'))
                                ->appendTag(ParamTag::create()->setVariable('response')->setType('ResponseInterface'))
                                ->appendTag(ParamTag::create()->setVariable('args')->setType('array'))
                    )->setDescription($op->getSummary())->setLongDescription($op->getDescription());
                    $method->addParameter(PhpParameter::create('request')->setType('ServerRequestInterface'));
                    $method->addParameter(PhpParameter::create('response')->setType('ResponseInterface'));
                    $method->addParameter(PhpParameter::create('args')->setType('array'));

                    $controllers[$name]->setMethod($method);
                }
            }
        }

        $gen = new CodeGenerator();

        foreach($controllers as $controller) {
            var_dump($gen->generate($controller));
        }
    }
}