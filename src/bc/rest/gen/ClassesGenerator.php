<?php

namespace bc\rest\gen;

use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\Operation;
use gossi\swagger\Parameter;
use gossi\swagger\Path;
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
     * @var ModelGenerator
     */
    private $models;
    /**
     * @var ControllerGenerator
     */
    private $controllers;
    /**
     * @var array
     */
    private $groups;
    /**
     * @var array
     */
    private $configs;
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
        $this->models = new ModelGenerator($this->swagger, $this->namespace);
        $this->configs = [];
        $this->createPathGroups();
        $this->controllers = new ControllerGenerator($this->swagger, $this->namespace, $this->groups);
        $this->createBootstrap();
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
        $bootstrap = new PhpClass('Bootstrap');
        $bootstrap
            ->setNamespace($this->namespace)
            ->addUseStatement('Slim\\App')
            ->addUseStatement('Slim\\Http\\Request')
            ->addUseStatement('Slim\\Http\\Response')
            ->setDescription('Class Bootstrap')
            ->setLongDescription('Creating routes and starting application')
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $this->namespace)))
            ->setConstant('BAD_HTTP_CODES',
                          '[400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411,
                            412, 413, 414, 415, 416, 417, 500, 501, 502, 503, 504, 505]', true)
            ->setProperty(PhpProperty::create('app')
                                     ->setType('App')
                                     ->setDescription('Slim application')
                                     ->setVisibility('private'));

        $construct = PhpMethod::create('__construct')->setDescription('Bootstrap constructor');
        $construct->addParameter(PhpParameter::create('app')->setType('App')->setValue(null));

        $routes = [];
        $routes[] = '$this->app = is_null($app) ? new App() : $app;';
        $routes[] = "\$this->app->add('{$bootstrap->getQualifiedName()}::processRequest');";

        foreach($this->groups as $group => $info) {
            $ctrl = ucfirst(strtolower($group)).'Controller';
            $controller = $this->controllers->get($ctrl);
            $routeMethod = PhpMethod::create('routeTo'.$controller->getName());
            $path = rtrim($this->swagger->getBasePath(), '/').'/'.$group;
            $routeMethod
                ->setVisibility('private')
                ->setDescription('Route to '.$path.' api group');
            $body = [];

            $defaultConfig = [];

            $subBody = [];
            $subBody[] = 'function () use($apiConfig) {';
            $subBody[] = "\t/** @var App \$this */";
            foreach($info as $item) {
                /** @var Path $currentPath */
                $currentPath = $item['path'];
                /** @var Operation $op */
                $op = $item['operation'];
                $p = str_replace('/'.$group, '', $currentPath->getPath());
                if($currentPath->getParameters()->size() > 0) {
                    /**
                     * @var string $key
                     * @var Parameter $val
                     */
                    foreach($currentPath->getParameters() as $val) {
                        if($val->getIn() == 'path'
                           && strpos($p, $val->getName()) !== false
                           && !empty($val->getPattern())
                        ) {
                            $p = str_replace(
                                '{'.$val->getName().'}',
                                '{'.$val->getName().':'.trim($val->getPattern(), '/').'}', $p);
                        }
                    }
                }
                $subBody[] = "\t".'if($apiConfig[\''.$op->getOperationId().'\'])';
                $method = strtolower($item['method']);
                $subBody[] = "\t\t\$this->$method('".$p."', '\\".$controller->getQualifiedName().':'.$op->getOperationId().'\');';
                $defaultConfig[$op->getOperationId()] = true;
            }
            $subBody[] = '}';

            $this->configs[$ctrl] = $defaultConfig;

            $body[] = '$settings = $this->app->getContainer()->get(\'settings\');';
            $body[] = '$apiConfig = '.var_export($defaultConfig, true).';';
            $body[] = 'if(isset($settings[\'api\']) && isset($settings[\'api\'][\''.$ctrl.'\'])) { ';
            $body[] = "\t".'$apiConfig = array_merge($apiConfig, $settings[\'api\'][\''.$ctrl.'\']);';
            $body[] = '}';
            $body[] = '$this->app->group(\''.$path.'\', '.implode("\n", $subBody).');';

            $routeMethod->setBody(implode("\n", $body));

            $routes[] = '$this->'.$routeMethod->getName().'();';
            $bootstrap->setMethod($routeMethod);
        }
        $construct->setBody(implode("\n", $routes));

        $bootstrap->setMethod($construct);

        $runner = PhpMethod::create('run')->setDescription('Start application');
        $runner->setBody('$this->app->run();');
        $bootstrap->setMethod($runner);

        $getInfo =
            PhpMethod::create('getInfo')
                     ->setStatic(true)
                     ->setType('array')
                     ->setDescription('Return generated info from specs')
                     ->setBody('return '.var_export($this->swagger->getInfo()->toArray(), true).';');

        $bootstrap->setMethod($getInfo);

        $bootstrap->setMethod(
            PhpMethod::create('processRequest')
                     ->setType('Response')
                     ->setStatic(true)
                     ->addSimpleParameter('request', 'Request')
                     ->addSimpleParameter('response', 'Response')
                     ->addSimpleParameter('next', 'callable')
                     ->setBody(<<<BODY
try {
    /** @var Response \$response */
    \$response = \$next(\$request, \$response);
    if(in_array(\$response->getStatusCode(), self::BAD_HTTP_CODES)) {
        throw new \Exception(\$response->getReasonPhrase(), \$response->getStatusCode());
    }
}
catch(\\Exception \$e) {
    \$json = [
                'message'   => \$e->getMessage(),
                'code'      => \$e->getCode(),
                'exception' => get_class(\$e)
            ];
    return \$response
        ->withStatus(
            in_array(\$e->getCode(), self::BAD_HTTP_CODES) ? \$e->getCode() : 500)
        ->withJson(\$json);
}
return \$response->withStatus(204, 'Request processed');
BODY
                     )
        );

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
            case 'file':
                return 'UploadedFile';
            default:
                return $type;
        }
    }

    /**
     * @return PhpClass[]
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
     * @return PhpClass[]
     */
    public function getModels() {
        return $this->models->getAll();
    }

    /**
     * @return array
     */
    public function getConfigs() {
        return $this->configs;
    }
}