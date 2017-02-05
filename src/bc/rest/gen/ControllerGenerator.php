<?php

namespace bc\rest\gen;

use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\docblock\Docblock;
use gossi\docblock\tags\ReturnTag;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\collections\Parameters;
use gossi\swagger\Operation;
use gossi\swagger\Parameter;
use gossi\swagger\Path;
use gossi\swagger\Response;
use gossi\swagger\Swagger;

class ControllerGenerator {

    /**
     * @var Swagger
     */
    private $swagger;
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var PhpClass[]
     */
    private $controllers;
    private $groups;

    /**
     * ControllerGenerator constructor.
     *
     * @param Swagger $swagger
     * @param string $namespace
     * @param $groups
     */
    public function __construct(Swagger $swagger, $namespace, $groups) {
        $this->swagger = $swagger;
        $this->namespace = $namespace;
        $this->groups = $groups;
        $this->controllers = [];
        $this->createControllers();
    }

    private function createControllers() {

        /** @var PhpClass[] $controllers */
        $controllers = [];
        foreach($this->groups as $group => $info) {
            $name = ucfirst(strtolower($group)).'Controller';
            if(!isset($controllers[$name])) {
                $path = rtrim($this->swagger->getBasePath(), '/').'/'.$group;
                $controllers[$name] = new PhpClass($name);
                $controllers[$name]
                    ->setNamespace($this->namespace.'\\Controllers')
                    ->setParentClassName("AbstractController")
                    ->setUseStatements(
                        [
                            'Rest\\Lib\\AbstractController',
                            'Slim\\Http\\Request',
                            'Slim\\Http\\Response'
                        ])
                    ->setDocblock(
                        Docblock::create()
                                ->appendTag(TagFactory::create('package', $controllers[$name]->getNamespace())))
                    ->setDescription('Class '.$name)
                    ->setLongDescription('Handle '.$path);
            }

            foreach($info as $item) {
                /** @var Operation $op */
                $op = $item['operation'];
                /** @var Path $path */
                $path = $item['path'];
                if(!empty($op->getOperationId())) {
                    $method = PhpMethod::create($op->getOperationId());

                    $doc = Docblock::create()
                                   ->appendTag(ReturnTag::create()->setType('Response'))
                                   ->appendTag(TagFactory::create('api', strtoupper($item['method']).' '.$path->getPath()));

                    $body = [];
                    $uses = [];

                    $internal = $this->parseParams($path->getParameters());
                    $internal = array_merge($internal, $this->parseParams($op->getParameters()));

                    $internalInit = [];

                    $useFiles = false;

                    foreach($internal as $paramName => $p) {
                        if(isset($p['param'])) {
                            /** @var Parameter $genParam */
                            $genParam = $p['param'];
                            if($genParam->getType() == "file") {
                                $useFiles = true;
                            }
                            if(isset($p['body'])) {
                                $uses[] = '$'.$paramName;
                            }
                            $type = ClassesGenerator::getType($genParam->getType());
                            if(!empty($type)) {
                                $internalInit[] = '/** @var '.$type.' $'.$paramName.' */';
                            }
                        }
                        if(isset($p['tag'])) $doc->appendTag($p['tag']);
                        if(isset($p['body'])) $internalInit[] = $p['body'];
                        if(isset($p['usage'])) $controllers[$name]->addUseStatement($p['usage']);
                    }

                    if($useFiles) {
                        $body[] = '$files = $request->getUploadedFiles();';
                    }

                    $body = array_merge($body, $internalInit);

                    $responses = $op->getResponses();

                    /**
                     * @var string $r
                     * @var Response $response
                     */
                    foreach($responses as $r => $response) {
                        $ref = explode('/', $response->getSchema()->getRef());
                        $refName = $ref[count($ref) - 1];
                        if($this->swagger->getDefinitions()->has($refName)) {
                            $doc->appendTag(
                                TagFactory::create('api-response:'.$r,
                                                   $this->namespace.'\\ApiModels\\'.$refName.' '.$response->getDescription()));
                        }
                        else {
                            $doc->appendTag(
                                TagFactory::create('api-response:'.$r, $response->getDescription()));
                        }
                    }

                    $method->setDocblock($doc)
                           ->setLongDescription($op->getDescription());
                    $method->setDescription($op->getSummary());

                    $method->addParameter(PhpParameter::create('request')->setType('Request'));
                    $method->addParameter(PhpParameter::create('response')->setType('Response'));
                    $method->addParameter(PhpParameter::create('args')->setType('array'));

                    $body = $this->appendToDo($body, $method);

                    $body[] = '';
                    $body[] = "return \$response->withStatus(501, ".
                              "'{$controllers[$name]->getName()}::{$method->getName()} not implemented');";

                    $method->setBody(implode("\n", $body));

                    $controllers[$name]->setMethod($method);
                }
            }
        }

        $this->controllers = $controllers;
    }

    /**
     * @param Parameters $params
     *
     * @return array
     */
    private function parseParams($params) {
        $result = [];
        if($params->size() > 0) {
            /** @var Parameter $param */
            foreach($params as $param) {
                $name = $param->getName();
                if($param->getExtensions()->has('export') && !$param->getExtensions()->get('export', true)) {
                    continue;
                }
                if(isset($result[$name])) continue;
                $r = [];
                switch($param->getIn()) {
                    case 'body':
                        if($param->getSchema()->hasRef()) {
                            $ref = explode('/', $param->getSchema()->getRef());
                            $refName = $ref[count($ref) - 1];
                            if($this->swagger->getDefinitions()->has($refName)) {
                                $model = $refName.' $'.lcfirst($refName);
                                $r['param'] = $param;
                                $r['param']->setType($refName);
                                $r['tag'] = TagFactory::create('internal', $model);
                                $r['body'] = '$'.lcfirst($refName).' = new '.$refName.'($request->getParsedBody());';
                                $r['usage'] = $this->namespace.'\\ApiModels\\'.$refName;
                                $result[lcfirst($refName)] = $r;
                            }
                        }
                        break;
                    case 'formData':
                        $r['param'] = $param;
                        if($param->getType() == "file") {
                            $r['body'] =
                                sprintf("\$%s = isset(\$files['%s']) ? \$files['%s'] : null;",
                                        $name, $name, $name);
                            $r['usage'] = 'Slim\\Http\\UploadedFile';
                        }
                        else {
                            $r['body'] =
                                sprintf("\$%s = \$request->getParam('%s', %s);",
                                        $name, $name,
                                        is_null($param->getDefault()) ? 'null' : $param->getDefault());
                        }
                        break;
                    case 'path':
                        $r['param'] = $param;
                        $r['body'] =
                            sprintf('$%s = $args[\'%s\'];', $name, $name);
                        break;
                    case 'query':
                        $r['param'] = $param;
                        $r['body'] =
                            sprintf('$%s = $request->getQueryParam(\'%s\'%s);'
                                , $name, $name
                                , ', '.(is_null($param->getDefault()) ? 'null' : $param->getDefault()));
                        break;
                }

                if(count($r) > 0 && $name != 'body') {
                    $r['tag'] = TagFactory::create('internal',
                                                   ClassesGenerator::getType($param->getType())
                                                   .' $'.$name
                                                   .' '.$param->getDescription());
                    $result[$name] = $r;
                }

                /*if($param->getIn() == 'body') continue;
                $doc->appendTag(
                    TagFactory::create('internal',
                                       $this->getType($param->getType())
                                       .' $'.$param->getName()
                                       .' '.$param->getDescription()));*/
            }
        }

        return $result;
    }

    /**
     * @param array $body
     * @param PhpMethod $method
     *
     * @return array
     */
    private function appendToDo($body, $method) {
        $body[] = '';
        $body[] = '//TODO Method '.$method->getName().' not implemented';

        return $body;
    }

    /**
     * @return array|PhpClass[]
     */
    public function getAll() {
        return $this->controllers;
    }

    /**
     * @param $ctrl
     *
     * @return PhpClass
     */
    public function get($ctrl) {
        return $this->controllers[$ctrl];
    }

}