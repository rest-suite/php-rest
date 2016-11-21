<?php


namespace bc\rest\gen;

use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\ReturnTag;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\Schema;
use gossi\swagger\Swagger;
use phootwork\collection\ArrayList;

class ModelGenerator {

    /** @var Swagger */
    private $swagger;
    /** @var PhpClass[] */
    private $models;
    /** @var  string */
    private $namespace;

    /**
     * ModelGenerator constructor.
     *
     * @param Swagger $swagger
     * @param string $namespace
     */
    public function __construct(Swagger $swagger, $namespace) {
        $this->swagger = $swagger;
        $this->namespace = $namespace;
        $this->createModels();
    }


    private function createModels() {
        $this->models = [];

        $defs = $this->swagger->getDefinitions();
        /** @var Schema $def */
        foreach($defs as $name => $def) {
            if(isset($this->models[$name])) continue;
            if($def->getType() != 'object') continue;
            if($def->getExtensions()->has('export') && !$def->getExtensions()->get('export', true)) {
                continue;
            }
            $ns = $this->namespace.'\\Models';
            $model = new PhpClass($name);
            $model
                ->setNamespace($ns)
                ->setParentClassName('bc\\model\\Model')
                ->setLongDescription($def->getDescription())
                ->setDescription('Class '.$name)
                ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $ns)));

            $this->models[$name] = $model;
        }

        foreach($defs as $name => $def) {
            if(!isset($this->models[$name])) continue;
            $model = $this->models[$name];
            $this->createProperties($def, $model);

//            $construct = PhpMethod::create('__construct');
//            $construct->setDescription($model->getName().' constructor')
//                      ->addParameter(
//                          PhpParameter::create('data')->setType('array')->setDescription('Initial object data')
//                      );

            $checks = [];
            $body = [];
            $unchecked = [];

            foreach($model->getProperties() as $property) {
                /** @var Docblock $doc */
                $doc = $property->getDocblock();
                if($doc->getTags('required')->size() > 0) {
                    $checks[]
                        = 'if(!isset($data[\''.$property->getName().'\']))'."\n"
                          ."\t".' throw new \InvalidArgumentException("Property \''.$property->getName().'\' is required", 400);';
                    $unchecked[] = '$this->'.$property->getName().' = $data[\''.$property->getName().'\'];';
                }
                else {
                    $body[] = 'if(isset($data[\''.$property->getName().'\'])) $this->'
                              .$property->getName().' = $data[\''.$property->getName().'\'];';
                }
            }
            if(count($checks) > 0) $checks[] = '';

            $body = array_merge($checks, $body);

            if(count($unchecked) > 0) $body[] = '';
//            $body = array_merge($body, $unchecked);

//            $construct->setBody(implode("\n", $body));

//            $model->setMethod($construct);
        }
    }

    /**
     * @param Schema $def
     * @param PhpClass $model
     */
    private function createProperties($def, $model) {
        /** @var ArrayList $required */
        $required = $def->getRequired();

        $toArray = [];

        /** @var Schema $property */
        foreach($def->getProperties() as $param => $property) {
            $prop = PhpProperty::create($param)
                               ->setVisibility('private')
                               ->setDescription($property->getDescription());

            if(!is_null($required) && $required->contains($param)) {
                $prop->setDocblock(Docblock::create()->appendTag(TagFactory::create('required')));
            }

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


            $setter = PhpMethod::create('set'.ucfirst($param))
                               ->setDescription('Set '.$param.' property new value');
            $parameter = PhpParameter::create($param);
            if(strpos($prop->getType(), '[]') === false) {
                $parameter->setType($prop->getType());
            }
            $setter->addParameter($parameter);
            $setterBody = [];
            if($property->isReadOnly()) {
                $setterBody[] = 'if(!is_null($this->'.$param.')) {';
                $setterBody[] = "\t".'throw new \\LogicException(\'Property "'.$param.'" is read only\');';
                $setterBody[] = '}';
            }
            $setterBody[] = '$this->'.$param.' = $'.$param.';';
            $setter->setBody(implode("\n", $setterBody));
            $model->setMethod($setter);

            $model->setProperty($prop);
            $toArray[] = '\''.$prop->getName().'\' => $this->'.$getter->getName().'()';
        }

        $model->setMethod(
            PhpMethod::create('toArray')
                     ->setType('array')
                     ->setDescription('Return object as array')
                     ->setBody("return [\n\t".implode(",\n\t", $toArray)."\n];"));
    }

    public function getAll() {
        return $this->models;
    }

}