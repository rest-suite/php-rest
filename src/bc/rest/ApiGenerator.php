<?php

namespace bc\rest;

use bc\rest\commands\GenerateAll;
use Symfony\Component\Console\Application;

class ApiGenerator extends Application {

    public function __construct($name, $version) { 
        parent::__construct($name, $version);
        
        $this->add(new GenerateAll());
    }
    
}