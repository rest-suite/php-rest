<?php

namespace bc\rest;

use bc\rest\gen\ClassesGenerator;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use Symfony\Component\Console\Output\OutputInterface;

class Builder {

    const OPT_NAMESPACE = 'namespace';
    const OPT_OUTPUT = 'output';
    const OPT_ALL = 'all';
    const OPT_MODELS = 'models';
    const OPT_CONTROLLERS = 'controllers';
    const OPT_TESTS = 'tests';
    const OPT_SETTINGS = 'settings';
    const OPT_OUTPUT_PATH = 'outputPath';
    const OPT_SWAGGER = 'swagger';

    /**
     * @var array
     */
    private $options = [];
    /**
     * @var CodeGenerator
     */
    private $gen;
    /**
     * @var ClassesGenerator
     */
    private $classes;
    /**
     * @var string
     */
    private $srcPath;

    /**
     * Builder constructor.
     *
     * @param array $options
     * @param OutputInterface $output
     */
    public function __construct(array $options, $output) {
        if(count($options) != 8) throw new \InvalidArgumentException();
        $this->options = $options;
        $this->output = $output;
        $this->gen = new CodeGenerator(['generateEmptyDocblock' => false]);
        $this->classes = new ClassesGenerator($this->options[self::OPT_SWAGGER], $this->options[self::OPT_NAMESPACE]);
        $this->srcPath = $this->options[self::OPT_OUTPUT_PATH].DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;
    }

    public function build() {
        $mask = umask(0);

        $this->writeControllers();
        $this->writeModels();
        $this->writeConfigs();
        $this->writeTests();
        $this->writeComposerJson();

        umask($mask);
    }

    private function writeControllers() {
        if($this->options[self::OPT_ALL] || $this->options[self::OPT_CONTROLLERS]) {
            $this->writeClass($this->classes->getBootstrap());
            $this->output->writeln("Bootstrap created");

            foreach($this->classes->getControllers() as $controller) {
                $this->writeClass($controller);
                $this->output->writeln($controller->getName()." created");
            }
        }
    }

    /**
     * @param PhpClass $class
     */
    private function writeClass($class) {
        $path = $this->srcPath.$this->getPathFromNamespace($class->getNamespace());
        if(!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents($path.DIRECTORY_SEPARATOR.$class->getName().'.php',
                          "<?php\n\n".$this->gen->generate($class));
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    private function getPathFromNamespace($className) {
        return str_replace('\\', DIRECTORY_SEPARATOR, $className);
    }

    private function writeModels() {
        if($this->options[self::OPT_ALL] || $this->options[self::OPT_MODELS]) {
            foreach($this->classes->getModels() as $model) {
                $this->writeClass($model);
                $this->output->writeln($model->getName()." created");
            }
        }
    }

    private function writeConfigs() {
        if($this->options[self::OPT_ALL] || $this->options[self::OPT_SETTINGS]) {
            $configPath = $this->options[self::OPT_OUTPUT_PATH].DIRECTORY_SEPARATOR.'config';
            if(!file_exists($configPath)) {
                mkdir($configPath, 0755, true);
            }

            $configs = $this->classes->getConfigs();
            $fileName = $configPath.DIRECTORY_SEPARATOR.'api.php.dist';
            file_put_contents($fileName, "<?php \n\n return ".var_export($configs, true).';');
            $this->output->writeln("Config created");
        }
    }

    private function writeComposerJson() {
        $path = $this->options[self::OPT_OUTPUT_PATH].DIRECTORY_SEPARATOR.'composer.json';
        $ns = explode('\\', $this->options[self::OPT_NAMESPACE]);
        $vendor = array_shift($ns);
        $json = [
            'name'              => strtolower($vendor).'/'.strtolower(implode('-', $ns)),
            'minimum-stability' => 'dev',
            'require'           => [
                'slim/slim' => '3.5.0',
            ],
            'require-dev'       => [
                'bc/php-rest'             => '*',
                'codeception/codeception' => '2.2.5'
            ],
            'autoload'          => [
                'psr-4' => [
                    $this->options[self::OPT_NAMESPACE].'\\' =>
                        './src/'.str_replace(
                            DIRECTORY_SEPARATOR, '/',
                            $this->getPathFromNamespace($this->options[self::OPT_NAMESPACE])
                        ).'/'
                ]
            ],
            'repositories'      => [
                [
                    'type' => 'git',
                    'url'  => 'https://github.com/theinpu/swagger.git'
                ]
            ]
        ];
        file_put_contents($path, json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $this->output->writeln("composer.json created");
    }

    private function writeTests() { 
        //TODO
    }
}