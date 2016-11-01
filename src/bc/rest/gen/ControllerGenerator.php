<?php

namespace bc\rest\gen;

use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
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
                    ->setUseStatements(
                        [
                            'Slim\\Container',
                            'Slim\\Http\\Request',
                            'Slim\\Http\\Response'
                        ])
                    ->setDocblock(
                        Docblock::create()
                                ->appendTag(TagFactory::create('package', $controllers[$name]->getNamespace())))
                    ->setDescription('Class '.$name)
                    ->setLongDescription('Handle '.$path)
                    ->setProperties(
                        [
                            PhpProperty::create('ci')
                                       ->setType('Container')
                                       ->setDescription('Dependency injection container')
                                       ->setVisibility('private')
                        ])
                    ->setMethod(
                        PhpMethod::create('__construct')
                                 ->addParameter(
                                     PhpParameter::create('ci')->setType('Container'))
                                 ->setBody('$this->ci = $ci;'));
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

                    $internal = $this->parseParams($path->getParameters());
                    $internal = array_merge($internal, $this->parseParams($op->getParameters()));

                    $internalInit = [];

                    foreach($internal as $p) {
                        if(isset($p['tag'])) $doc->appendTag($p['tag']);
                        if(isset($p['body'])) $internalInit[] = $p['body'];
                        if(isset($p['usage'])) $controllers[$name]->addUseStatement($p['usage']);
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
                                                   $this->namespace.'\\Models\\'.$refName.' '.$response->getDescription()));
                        }
                        else {
                            $doc->appendTag(
                                TagFactory::create('api-response:'.$r, $response->getDescription()));
                        }
                    }

                    $method->setDocblock($doc)
                           ->setDescription($op->getSummary())
                           ->setLongDescription($op->getDescription());

                    $method->addParameter(PhpParameter::create('request')->setType('Request'));
                    $method->addParameter(PhpParameter::create('response')->setType('Response'));
                    $method->addParameter(PhpParameter::create('args')->setType('array'));

                    $body = $this->appendToDo($body, $name, $method);

                    $body[] = '';
                    $body[] = 'return $response;';

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
                                $r['tag'] = TagFactory::create('internal', $model);
                                $r['body'] = '$'.lcfirst($refName).' = new '.$refName.'($request->getParsedBody());';
                                $r['usage'] = $this->namespace.'\\Models\\'.$refName;
                                $result[$name] = $r;
                            }
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
     * @param string $name
     * @param PhpMethod $method
     *
     * @return array
     */
    private function appendToDo($body, $name, $method) {
        $body[] = '';
        $body[] = '//TODO Method '.$method->getName().' not implemented';
        $body[] = sprintf('$response = $response->withStatus(501, \'%s::%s not implemented\');',
                          $name, $method->getName());

        return $body;
    }

    /**
     * @return array|\gossi\codegen\model\PhpClass[]
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