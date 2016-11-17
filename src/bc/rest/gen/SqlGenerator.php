<?php
namespace bc\rest\gen;


use gossi\codegen\model\PhpClass;
use gossi\swagger\Schema;
use gossi\swagger\Swagger;
use phootwork\collection\ArrayList;
use phootwork\collection\Map;

class SqlGenerator{

    /** @var Swagger */
    private $swagger;

    /** @var PhpClass[] */
    private $sqls;

    /** @var  string */
    private $namespace;

    public function __construct(Swagger $swagger, $namespace) {
        $this->swagger = $swagger;
        $this->namespace = $namespace;
        $this->createSqls();
    }

    private function createSqls(){

        $defs = $this->swagger->getDefinitions();

        $this->sqls = [];


        /** @var Schema $def */
        foreach ($defs as $name => $def) {
            if(isset($this->sqls[$name])) continue;
            if($def->getType() != 'object') continue;
            if($this->isSimpleModel($def)) continue;

            $sql['content'] = $this->createSql($def, $name);
            $sql['nameSpace'] = $this->namespace.'\\Sqls';
            $sql['name'] = $name;

            $this->sqls[] = $sql;
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

    private function createSql($def, $name){
        
        $sqlIndices = $this->getSqlIndices($def);
        
        $sql = "CREATE TABLE `". strtolower($name) ."` (\n";
        
        /** @var Schema $def */
        foreach ($def->getProperties() as $name => $property) {
            $sqlParams  = $this->getSqlParams($property);
            $sql       .= $this->getColumn($name, $sqlParams);
            $sql       .= "\n";
        }

        $sql .= $this->getPrimaryKey($sqlIndices);
        $sql .= $this->getIndices($sqlIndices);
        $sql .= "\n)" ;

        
        ;

        return $sql;
    }

    private function getColumn($name, $sqlParams){
        
        $column  = "\t`" . $name . "` " . $sqlParams['type']  ; 
        
        $column .= (isset($sqlParams['not_null']) && $sqlParams['not_null'] === true) ? " NOT NULL" : '';
        
        $column .= (isset($sqlParams['auto_increment']) && $sqlParams['auto_increment'] == true) ? " AUTO_INCREMENT" : '';
        
        $column .= (isset($sqlParams['default'])) ? " DEFAULT " . $sqlParams['default'] : '';
        
        return $column . ',';
    }

    /**
     * @param Schema $def
     * @return array
     */
    private function getSqlIndices($def){
        /** @var Map $extensions */
        $extensions = $def->getExtensions();

        /** @var ArrayList $sqlIndices */
        $sqlIndices = $extensions->get('sql-indices');

        $sqlIndicesArr = [];

        /** @var Map $sqlIndex */
        foreach ($sqlIndices as $sqlIndex) {
            foreach ($sqlIndex as $name => $item) {

                $itemArr = [];
                /** @var Map $extensions */

                foreach ($item as $subName => $subItem) {
                    $subSubItemArr = [];
                    if(gettype($subItem) == 'object'){

                        /** @var ArrayList $subSubItem */
                        foreach ($subItem as $subSubName => $subSubItem) {
                            $subSubItemArr[$subSubName] = $subSubItem;
                        }

                    } elseif (gettype($subItem) == 'string'){
                        
                        $subSubItemArr = $subItem;
                    }
                   
                    $itemArr[$subName] = $subSubItemArr;
                } 
                $sqlIndicesArr[$name] = $itemArr;
            }
        }
        
        return $sqlIndicesArr;        
    }

    private function getIndices($sqlIndices){

        $sqlIndicesString = '';

        foreach ($sqlIndices as $sqlIndex) {
            if($sqlIndex['type'] == 'index'){

                $sqlIndicesString .= "\tINDEX ";

                $values = '(';

                $count = count($sqlIndex['fields']);
                $i=0;
                foreach ($sqlIndex['fields'] as $field) {
                    $i++;

                    $sqlIndicesString .= $field . '_';

                    $values .= $field ;
                    if($i >= $count) continue;
                    $values .= ', ';
                }
                $sqlIndicesString .= 'index ' . $values . ') ';
                
                if(isset($sqlIndex['size'])){
                    
                 
                    
                    $sqlIndicesString .= " KEY_BLOCK_SIZE " . (int)$sqlIndex['size'];
                }
            }
        }

        if(!empty($sqlIndicesString)){
            $sqlIndicesString = ",\n" . $sqlIndicesString;
        }
        
        return $sqlIndicesString;
    }


    private function getPrimaryKey($sqlIndices){
        $pk = '';
        foreach ($sqlIndices as $name => $sqlIndex) {
            if($sqlIndex['type'] == 'primary'){
                $pk = $name;
            }
        }
        return ($pk != '') ? "\tPRIMARY KEY (`" . $pk ."`)" : '';
    }

    /**
     * @param Schema $property
     * @return array
     */
    private function getSqlParams($property){

        /** @var Map $extensions */
        $extensions = $property->getExtensions();

        /** @var ArrayList $sql */
        $sql = $extensions->get('sql');
        
        $sqlParams = [];

        /** @var Map $sqlParam */
        foreach ($sql as $sqlParam) {
            $sqlParamArr = $sqlParam->toArray();

            foreach ($sqlParamArr as $name => $value) {
                $sqlParams[$name] = $value;
            }
        }
        return $sqlParams;
    }

    public function getAll() {
        return $this->sqls;
    }
}