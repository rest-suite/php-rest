<?php
namespace bc\rest\gen;

use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\Schema;
use gossi\swagger\Swagger;
use phootwork\collection\ArrayList;
use phootwork\collection\Map;

class DataMapGenerator {
    
    /** @var Swagger */
    private $swagger;
    /** @var PhpClass[] */
    private $dataMaps;
    /** @var  string */
    private $namespace;

    public function __construct(Swagger $swagger, $namespace) {
        $this->swagger = $swagger;
        $this->namespace = $namespace;
        $this->createDataMaps();
    }

    private function createDataMaps() {
        $this->dataMaps = [];

        $defs = $this->swagger->getDefinitions();

        /** @var Schema $def */
        foreach ($defs as $name => $def) {
            if(isset($this->dataMaps[$name])) continue;
            if($def->getType() != 'object') continue;
            if($this->isSimpleModel($def)) continue;
            
            $ns = $this->namespace.'\\DataMaps';

            $dataMap = new PhpClassWrapper($name . 'DataMap');
            $dataMap
                ->setNamespace($ns)
                ->addUseStatement($name . '\\Models\\' .$name)
                ->addUseStatement('bc\model\DataMap')
                ->setLongDescription($def->getDescription())
                ->setDescription('Class '.$name . 'DataMap')
                ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $ns)))
                ->setParentClassName('DataMap')
                ->setDef($def)
                ->setTableName($name)

            ;

            $dataMap = $this->createUpdateBindings($dataMap);
            $dataMap = $this->createInsertBindings($dataMap);
            $dataMap = $this->createInitSql($dataMap);

            $this->dataMaps[$name] = $dataMap;
        }
    }

    /**
     * @param Schema $def
     * @return bool
     */
    private function isSimpleModel($def){

        foreach ($def->getProperties() as $name => $property) {
            /** @var Map $extensions */
            $extensions = $property->getExtensions();

            /** @var ArrayList $sql */
            $sql = $extensions->get('sql');

            if(is_null($sql)) return true;
        }

        return false;
    }

    /**
     * @param PhpClassWrapper $dataMap
     * @return PhpClassWrapper
     */
    private function createInitSql($dataMap) {

        $initSql = new PhpMethod('initSql');
        $name = $dataMap->getTableName();

        $fields = $dataMap->getTableFields();

        $findOneSql = "SELECT " . $fields . " FROM `" . $name . "` WHERE `id`=:id";
        $findAllSql = "SELECT " . $fields . " FROM `" . $name . '`';
        $findByIdsSql = "SELECT " . $fields . " FROM `". $name ."` WHERE `id` IN (:ids)";
        $deleteSql = "DELETE FROM `" . $name . "` where `id`=:id";
        $countSql = "SELECT count(`id`) FROM `". $name .'`';
        $insertSql = $this->generateInsertSql($dataMap);
        $updateSql = $this->generateUpdateSql($dataMap);

        $code = "\$this->className = '" . $name . '\\\\Models\\\\'.  ucfirst(strtolower($dataMap->getTableName())) . "';\n";
        $code .= "\$this->findOneSql = '$findOneSql';\n";
        $code .= "\$this->findAllSql = '$findAllSql';\n";
        $code .= "\$this->findByIdsSql = '$findByIdsSql';\n";
        $code .= "\$this->countSql = '" . $countSql . "';\n";
        $code .= "\$this->insertSql = '" . $insertSql . "';\n";
        $code .= "\$this->updateSql = '" . $updateSql . "';\n";
        $code .= "\$this->deleteSql = '" . $deleteSql ."';\n";
        
        $initSql
            ->setBody($code)
            ->setVisibility('protected')
        ;
        
        $dataMap->setMethod($initSql);
        
        return $dataMap;

    }

    /**
     * @param PhpClassWrapper $dataMap
     * @return string
     */
    private function generateInsertSql($dataMap){
              
        $sql = "INSERT INTO `" . $dataMap->getTableName() . "` (" . $dataMap->getTableFields('noid') .") VALUES (" . 
            $dataMap->getTableBindings('noid') . ');';
        return $sql;
    }

    /**
     * @param PhpClassWrapper $dataMap
     * @return string
     */
    private function generateUpdateSql($dataMap){

        $sql = "UPDATE `" . $dataMap->getTableName() . "` SET " . $dataMap->getTableUpdatePairs() . " WHERE `id`=:id;";
        return $sql;
    }

    /**
     * @param PhpClassWrapper $dataMap
     * @return PhpClassWrapper
     */
    private function createInsertBindings($dataMap){

        $getInsertBindings = new PhpMethod('getInsertBindings');
        $dataMap->setMethod($getInsertBindings);

        $code = "return [ \n";

        foreach ($dataMap->getDef()->getProperties()->toArray() as $name => $item) {
            if($name == 'id') continue;
            
            $code .= "\t':" . $name . "' => \$item->get" . ucfirst($name) . "()," . "\n";
        }

        $code .= '];';

        $item = new PhpParameter('item');
        $modelName = ucfirst(strtolower($dataMap->getTableName()));
//        $item->setType($modelName);


        $getInsertBindings
            ->setBody($code)
            ->addParameter($item)
            ->setType($modelName)
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('param ' . $modelName, '$item')))
            ->setVisibility('protected')
            ->generateDocblock()
        ;

        return $dataMap;
    }

    /**
     * @param PhpClassWrapper $dataMap
     * @return PhpClassWrapper
     */
    private function createUpdateBindings($dataMap){

        $getUpdateBindings = new PhpMethod('getUpdateBindings');
        $dataMap->setMethod($getUpdateBindings);
        
        $code = "return [ \n";

        foreach ($dataMap->getDef()->getProperties()->toArray() as $name => $item) {
            if($name == 'id') continue;
            $code .= "\t':" . $name . "' => \$item->get" . ucfirst($name) . "()," . "\n";
        }
       
        $code .= '];';
        
        $item = new PhpParameter('item');
        $modelName = ucfirst(strtolower($dataMap->getTableName()));
//        $item->setType($modelName);
       
        
        $getUpdateBindings
            ->setBody($code)
            ->addParameter($item)
            ->setType($modelName)
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('param ' . $modelName, '$item')))
            ->setVisibility('protected')
            ->generateDocblock()
        ;

        return $dataMap;
    }   
    
    
    public function getAll() {
        return $this->dataMaps;
    }
}