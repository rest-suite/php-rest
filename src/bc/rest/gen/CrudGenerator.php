<?php
namespace bc\rest\gen;


use gossi\swagger\Operation;
use gossi\swagger\Path;
use gossi\swagger\Response;
use gossi\swagger\Schema;

class CrudGenerator
{

    /**
     * @param Operation $operation
     * @return array
     */
    public function get($operation){
        
        return ["//TODO: implement crud for get", "//TODO: finish generator"];
    }

    
    private function isAutoField($name){
        return in_array($name, ['created_at', 'updated_at', 'id']);
    }

    /**
     * @param Schema $schema
     * @return array
     */
    private function generateBuildModel($schema){
        
        var_dump($schema->getType());
        var_dump($schema->getDescription());
        var_dump($schema->getRef());
        var_dump($schema->getTitle());

        $code = [];

        $code[] = "\n$".strtolower($schema->getTitle()) . ' = ' . $schema->getTitle() . 'Builder::create()';

        /** @var Schema $item */
        foreach ($schema->getProperties()->toArray() as $name => $item) {
            if($this->isAutoField($name)) continue;
            $code[] = "\t->" . $name . '($' . $name . ')';
        }
        $code[] = "\t->build();";
        
        $code[] = "\n\$factory = new " . $schema->getTitle() . "Factory(new " . $schema->getTitle() . 'DataMap());';
        $code[] = "\$factory->save($" . strtolower($schema->getTitle()) . ");";
        return $code;
    }
    
    
    /**
     * @param Schema $schema
     * @return array
     */
    public function post($schema){

        $code = [];
        
        /** @var Schema $item */
        foreach ($schema->getProperties()->toArray() as $name => $item) {
            if($this->isAutoField($name)) continue;
            $code[] = '$' . $name . ' = $request->getParsedBodyParam(\'' . $name . '\');';
            
          
        };
        $code = array_merge($code, $this->generateBuildModel($schema));
        
        return $code;
    }


    
    /**
     * @param Operation $operation
     * @return array
     */
    public function put($operation){
        return ["//TODO: implement crud for put", "//TODO: finish generator"];
    }

    /**
     * @param Operation $operation
     * @return array
     */
    public function delete($operation){
        return ["//TODO: implement crud for delete", "//TODO: finish generator"];
    }
    
    public static function generate(){
        return new self();
    }
}