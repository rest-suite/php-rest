<?php

namespace bc\rest\gen;

use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpFunction;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\ParamTag;
use gossi\docblock\tags\ReturnTag;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\collections\Parameters;
use gossi\swagger\Operation;
use gossi\swagger\Parameter;
use gossi\swagger\Path;
use gossi\swagger\Response;
use gossi\swagger\Schema;
use gossi\swagger\Swagger;
use Symfony\Component\Yaml\Yaml;

class ClassesGenerator {

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
    private $models;
    /**
     * @var ControllerGenerator
     */
    private $controllers;
    private $groups;
    /**
     * @var PhpClass
     */
    private $bootstrap;

    /**
     * ClassesGenerator constructor.
     *
     * @param string $swaggerFile
     * @param string $namespace
     */
    public function __construct($swaggerFile, $namespace) {
        $yml = file_get_contents($swaggerFile);
        $swagger = Yaml::parse($yml);
        $this->swagger = new Swagger($swagger);
        $this->namespace = rtrim($namespace, '\\');
        $this->createModels();
        $this->createPathGroups();
        $this->controllers = new ControllerGenerator($this->swagger, $this->namespace, $this->groups);

        $this->createBootstrap();
    }

    private function createModels() {
        $this->models = [];

        $defs = $this->swagger->getDefinitions();
        /** @var Schema $def */
        foreach($defs as $name => $def) {
            if(isset($this->models[$name])) continue;
            if($def->getType() != 'object') continue;
            $ns = $this->namespace.'\\Models';
            $model = new PhpClass($name);
            $model
                ->setNamespace($ns)
                ->setLongDescription($def->getDescription())
                ->setDescription('Class '.$name)
                ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $ns)));

            $this->models[$name] = $model;
        }

        foreach($defs as $name => $def) {
            if(!isset($this->models[$name])) continue;
            $model = $this->models[$name];
            /** @var Schema $property */
            foreach($def->getProperties() as $param => $property) {
                $prop = PhpProperty::create($param)
                                   ->setVisibility('private')
                                   ->setDescription($property->getDescription());

                switch($property->getType()) {
                    case 'integer':
                        $prop->setType('int');
                        break;
                    case 'boolean':
                        $prop->setType('bool');
                        break;
                    case 'array':
                        if($property->getItems()->hasRef()) {
                            $ref = explode('/', $property->getItems()->getRef());
                            $n = $ref[count($ref) - 1];
                            if($this->models[$n]->getNamespace() != $model->getNamespace()) {
                                $model->addUseStatement($this->models[$n]->getQualifiedName());
                            }
                            $prop->setType($this->models[$n]->getName().'[]');
                        }
                        else {
                            $prop->setType('array');
                        }
                        break;
                    default:
                        if($property->hasRef()) {
                            $ref = explode('/', $property->getRef());
                            $n = $ref[count($ref) - 1];
                            if(isset($this->models[$n])) {
                                if($this->models[$n]->getNamespace() != $model->getNamespace()) {
                                    $model->addUseStatement($this->models[$n]->getQualifiedName());
                                }
                                $prop->setType($this->models[$n]->getName());
                            }
                        }
                        else {
                            $prop->setType($property->getType());
                        }
                        break;
                }

                $getter = PhpMethod::create('get'.ucfirst($param));
                $getter->setDocblock(
                    Docblock::create()->appendTag(
                        ReturnTag::create($prop->getType())
                    )
                )
                       ->setDescription('Get '.$param.' property value')
                       ->setBody('return $this->'.$param.';');
                $model->setMethod($getter);

                if(!$property->isReadOnly()) {
                    $setter = PhpMethod::create('set'.ucfirst($param))
                                       ->setDescription('Set '.$param.' property new value');
                    $parameter = PhpParameter::create($param);
                    if(strpos($prop->getType(), '[]') === false) {
                        $parameter->setType($prop->getType());
                    }
                    $setter->addParameter($parameter);
                    $setter->setBody('$this->'.$param.' = $'.$param.';');
                    $model->setMethod($setter);
                }

                $model->setProperty($prop);
            }
        }
    }

    private function createPathGroups() {
        $paths = $this->swagger->getPaths();

        $this->groups = [];

        /** @var Path $path */
        foreach($paths as $path) {
            $elements = explode('/', ltrim($path->getPath(), '/'));
            if(count($elements) == 0) {
                $elements[0] = 'default';
            }
            if(!isset($this->groups[$elements[0]])) $groups[$elements[0]] = [];

            foreach(Swagger::$METHODS as $method) {
                if($path->hasOperation($method)) {
                    /** @var Operation $operation */
                    $operation = $path->getOperation($method);
                    $this->groups[$elements[0]][] = [
                        'method'    => $method,
                        'operation' => $operation,
                        'path'      => $path,
                    ];
                }
            }
        }
    }

    private function createBootstrap() {
        $gen = new CodeGenerator(['generateEmptyDocblock' => false]);
        $bootstrap = new PhpClass('Bootstrap');
        $bootstrap
            ->setNamespace($this->namespace)
            ->addUseStatement('Slim\\App')
            ->setDescription('Class Bootstrap')
            ->setLongDescription('Creating routes and starting application')
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $this->namespace)))
            ->setProperty(PhpProperty::create('app')
                                     ->setType('App')
                                     ->setDescription('Slim application')
                                     ->setVisibility('private'));

        $construct = PhpMethod::create('__construct')->setDescription('Bootstrap constructor');

        $routes = ['$this->app = new App();'];
        foreach($this->groups as $group => $info) {
            $ctrl = ucfirst(strtolower($group)).'Controller';
            $controller = $this->controllers->get($ctrl);
            $routeMethod = PhpMethod::create('routeTo'.$controller->getName());
            $path = rtrim($this->swagger->getBasePath(), '/').'/'.$group;
            $routeMethod
                ->setVisibility('private')
                ->setDescription('Route to '.$path.' api group');
            $body = [];

            $subBody = [];
            foreach($info as $item) {
                /** @var Path $currentPath */
                $currentPath = $item['path'];
                /** @var Operation $op */
                $op = $item['operation'];
                $p = str_replace('/'.$group, '', $currentPath->getPath());
                $subBody[] = '$this->app->'.strtolower($item['method'])
                             .'(\''.$p.'\', \'\\'.$controller->getQualifiedName().':'.$op->getOperationId().'\');';
            }
            $f = PhpFunction::create()->setBody(implode("\n", $subBody));

            $body[] = '$this->app->group(\''.$path.'\', '.$gen->generate($f).');';

            $routeMethod->setBody(implode("\n", $body));

            $routes[] = '$this->'.$routeMethod->getName().'();';
            $bootstrap->setMethod($routeMethod);
        }
        $construct->setBody(implode("\n", $routes));

        $bootstrap->setMethod($construct);

        $runner = PhpMethod::create('run')->setDescription('Start application');
        $runner->setBody('$this->app->run();');
        $bootstrap->setMethod($runner);

        $this->bootstrap = $bootstrap;
    }

    public static function getType($type) {
        switch($type) {
            case Swagger::T_INTEGER:
                return 'int';
            case Swagger::T_NUMBER:
                return 'int';
            case Swagger::T_BOOLEAN:
                return 'bool';
            case Swagger::T_STRING:
                return 'string';
            case 'array':
                return 'array';
            default:
                return '';
        }
    }

    /**
     * @return \gossi\codegen\model\PhpClass[]
     */
    public function getControllers() {
        return $this->controllers->getAll();
    }

    /**
     * @return PhpClass
     */
    public function getBootstrap() {
        return $this->bootstrap;
    }

    /**
     * @return \gossi\codegen\model\PhpClass[]
     */
    public function getModels() {
        return $this->models;
    }
}