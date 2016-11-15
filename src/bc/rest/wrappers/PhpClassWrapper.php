<?php

namespace bc\rest\gen;
use gossi\codegen\model\PhpClass;
use gossi\swagger\Schema;

/**
 * Created by PhpStorm.
 * User: id
 * Date: 14.11.16
 * Time: 15:59
 */
class PhpClassWrapper extends PhpClass
{
    
    private $tableName;

    /** @var Schema $def */
    private $def;

    /**
     * @return Schema
     */
    public function getDef()
    {
        return $this->def;
    }

    /**
     * @param mixed $def
     * @return PhpClassWrapper
     */
    public function setDef($def)
    {
        $this->def = $def;
        return $this;
    }
    
    public function __construct($name)
    {
        parent::__construct($name);
    }
    
    public function getTableName(){
        return $this->tableName;
    }

    public function setTableName($tableName){
        $this->tableName = strtolower($tableName);
    }

    public function getTableFields($params = null){

        $fieldsString   = '';
        $properties     = $this->def->getProperties();
        $count          = count($properties->toArray());

        $i=0;
        foreach ($properties as $name => $schema) {
            $i++;
            if(($params == 'noid') && ($name == 'id')) continue;

            $fieldsString .= "`" . $name . "`";
            if($i < $count){
                $fieldsString .= ", ";
            }
        }

        return $fieldsString;
    }

    public function getTableBindings($params = null){

        $bindingsString   = '';
        $properties     = $this->def->getProperties();
        $count          = count($properties->toArray());

        $i=0;
        foreach ($properties as $name => $schema) {
            $i++;
            if(($params == 'noid') && ($name == 'id')) continue;

            $bindingsString .= ":" . $name ;
            if($i < $count){
                $bindingsString .= ", ";
            }
        }

        return $bindingsString;
    }
    
    public function getTableUpdatePairs(){
        
        $updatePairsString   = '';
        $properties     = $this->def->getProperties();
        $count          = count($properties->toArray());

        $i=0;
        foreach ($properties as $name => $schema) {
            $i++;
            $updatePairsString .= "`" . $name ."`=:" . $name ;
            if($i < $count){
                $updatePairsString .= ", ";
            }
        }

        return $updatePairsString;
    }
}