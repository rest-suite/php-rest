<?php
namespace bc\rest\gen;


use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\Schema;
use gossi\swagger\Swagger;
use phootwork\collection\ArrayList;
use phootwork\collection\Map;

class BuilderGenerator{

    /** @var Swagger */
    private $swagger;

    /** @var PhpClass[] */
    private $builders;

    /** @var  string */
    private $namespace;

    public function __construct(Swagger $swagger, $namespace) {
        $this->swagger = $swagger;
        $this->namespace = $namespace;
        $this->createBuilders();
    }

    private function createBuilders() {
        $this->builders = [];

        $defs = $this->swagger->getDefinitions();
 
        /** @var Schema $def */
        foreach ($defs as $name => $def) {
            if(isset($this->builders[$name])) continue;
            if($def->getType() != 'object') continue;

            $ns = $this->namespace.'\\Builders';

            $builder = new PhpClassWrapper($name . 'Builder');
            $builder
                ->setNamespace($ns)
                ->setLongDescription($def->getDescription())
                ->setDescription('Class '.$name . 'Builder')
                ->setDocblock(
                    Docblock::create()->appendTag(TagFactory::create('package', $ns))

                )
                ->setParentClassName('IBuilder')
                ->setDef($def)
                ->setTableName($name)
                
            ;
            $builder = $this->addProperties($builder);
            $builder = $this->addChainSetters($builder);
            $builder = $this->addCreateMethod($builder);
            $builder = $this->addBuildMethod($builder);


            $this->builders[$name] = $builder;
        }
    }


    /**
     * @param PhpClassWrapper $builder
     * @return PhpClassWrapper
     */
    public function addBuildMethod($builder){
        
        $buildMethod = new PhpMethod('build');
        $modelName   = ucfirst(strtolower($builder->getTableName()));

        $body        = '';
        
        /** @var Schema $item */
        foreach ($builder->getDef()->getProperties() as $name => $item) {
            if ($name == 'id') continue;

            /** @var ArrayList $required */
            $required = $builder->getDef()->getRequired();
            
            if((!is_null($required)) && in_array($name, $required->toArray())){

                $body .= "if(is_null(\$this->" . $name . ")){\n";
                $body .= "\tthrow new \\InvalidArgumentException('Need to set " . $name." ');\n}\n";
                
            }
        }
        
        $body        .= "\$item = new " . $modelName . "(); \n";
    
        /** @var Schema $item */
        foreach ($builder->getDef()->getProperties() as $name => $item) {
            if ($name == 'id') continue;
            $body .= "\$item->set" .  ucfirst($name) . "(\$this->". $name .");\n";
        }   

            $buildMethod
            ->setType($modelName)
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('throws', '\InvalidArgumentException')))
            ->setBody($body)
        ;    
        
        
        
        $builder->setMethod($buildMethod);
        
        return $builder;
    }
    
    
    /**
     * @param PhpClassWrapper $builder
     * @return PhpClassWrapper
     */
    public function addCreateMethod($builder){
        
        $createMethod = new PhpMethod('create');
        $modelName  = ucfirst(strtolower($builder->getTableName()) . 'Builder');
        $body = 'return new self();';
        
        $createMethod
            ->setType($modelName)
            ->setBody($body)
        ;
        
        $builder->setMethod($createMethod);
        
        return $builder;
    }
    
    
    /**
     * @param PhpClassWrapper $builder
     * @return PhpClassWrapper
     */
    public function addChainSetters($builder){

        /** @var Schema $item */
        foreach ($builder->getDef()->getProperties() as $name => $item) {
            if($name == 'id') continue;

            $setter     = new PhpMethod($name);
            $param      = new PhpParameter($name);
            $body       = '$this->' . $name . " = \$$name; \nreturn \$this;";
            $modelName  = ucfirst(strtolower($builder->getTableName()) . 'Builder');

            $param->setType($item->getType());

            $setter
                ->addParameter($param)
                ->setType($modelName)
                ->setBody($body)
            ;

            $builder->setMethod($setter);
        }

        return $builder;
    }

    /**
     * @param PhpClassWrapper $builder
     * @return PhpClassWrapper
     */
    private function addProperties($builder){

        /** @var Schema $item */
        foreach ($builder->getDef()->getProperties() as $name => $item) {

            if($name == 'id') continue;
            
            $property = PhpProperty::create($name)
                ->setVisibility('private')
                ->setDescription($item->getDescription())
                ->setType($item->getType())
            ;
            
            $builder->setProperty($property);
        }

        return $builder;
    }
    

    public function getAll() {
        return $this->builders;
    }

}
