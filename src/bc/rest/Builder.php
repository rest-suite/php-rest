<?php

namespace bc\rest;

use bc\rest\gen\ClassesGenerator;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use Symfony\Component\Console\Output\OutputInterface;

class Builder
{

    const OPT_NAMESPACE = 'namespace';
    const OPT_OUTPUT = 'output';
    const OPT_ALL = 'all';
    const OPT_MODELS = 'models';
    const OPT_CONTROLLERS = 'controllers';
    const OPT_TESTS = 'tests';
    const OPT_SETTINGS = 'settings';
    const OPT_OUTPUT_PATH = 'outputPath';
    const OPT_SWAGGER = 'swagger';
    const OPT_OVERRIDE = 'override';

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
    public function __construct(array $options, $output)
    {
        if (count($options) != 9) throw new \InvalidArgumentException();
        $this->options = $options;
        $this->output = $output;
        $this->gen = new CodeGenerator(['generateEmptyDocblock' => false]);
        $this->classes = new ClassesGenerator($this->options[self::OPT_SWAGGER], $this->options[self::OPT_NAMESPACE]);
        $this->srcPath = $this->options[self::OPT_OUTPUT_PATH] . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    }

    public function build()
    {
        $mask = umask(0);

        $this->writeControllers();
        $this->writeModels();
        $this->writeConfigs();
        $this->writeTests();
        $this->writeComposerJson();

        umask($mask);
    }

    private function writeControllers()
    {
        if ($this->options[self::OPT_ALL] || $this->options[self::OPT_CONTROLLERS]) {
            if ($this->writeClass($this->classes->getBootstrap())) {
                $this->output->writeln("<info>Bootstrap created</info>");
            }

            foreach ($this->classes->getControllers() as $controller) {
                if ($this->writeClass($controller)) {
                    $this->output->writeln('<info>' . $controller->getName() . " created</info>");
                }
            }
        }
    }

    /**
     * @param PhpClass $class
     *
     * @return bool
     */
    private function writeClass($class)
    {
        $path = $this->srcPath . $this->getPathFromNamespace($class->getNamespace());
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $fileName = $path . DIRECTORY_SEPARATOR . $class->getName() . '.php';
        if (file_exists($fileName) && !$this->options[self::OPT_OVERRIDE]) {
            $this->output->writeln("<error>File '$fileName' exists</error>");

            return false;
        }

        file_put_contents($fileName, "<?php\n\n" . $this->gen->generate($class));

        return true;
    }

    /**
     * @param $className
     *
     * @return mixed
     */
    private function getPathFromNamespace($className)
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $className);
    }

    private function writeModels()
    {
        if ($this->options[self::OPT_ALL] || $this->options[self::OPT_MODELS]) {
            foreach ($this->classes->getModels() as $model) {
                if ($this->writeClass($model)) {
                    $this->output->writeln('<info>' . $model->getName() . " created</info>");
                }
            }
        }
    }

    private function writeConfigs()
    {
        if ($this->options[self::OPT_ALL] || $this->options[self::OPT_SETTINGS]) {
            $configPath = $this->options[self::OPT_OUTPUT_PATH] . DIRECTORY_SEPARATOR . 'config';
            if (!file_exists($configPath)) {
                mkdir($configPath, 0755, true);
            }

            $ns = explode('\\', $this->options[self::OPT_NAMESPACE]);
            $vendor = array_shift($ns);

            $configs = $this->classes->getConfigs();
            $fileName = $configPath . DIRECTORY_SEPARATOR
                . strtolower($vendor) . '-' . strtolower(implode('-', $ns)) . '.php.dist';
            
            if (file_exists($fileName) && !$this->options[self::OPT_OVERRIDE]) {
                $this->output->writeln("<error>File '$fileName' exists</error>");

                return;
            }
            file_put_contents($fileName, "<?php \n\n return " . var_export($configs, true) . ';');
            $this->output->writeln("<info>Config created</info>");
        }
    }

    private function writeTests()
    {
        //TODO
    }

    private function writeComposerJson()
    {
        $path = $this->options[self::OPT_OUTPUT_PATH] . DIRECTORY_SEPARATOR . 'composer.json';
        if (file_exists($path) && !$this->options[self::OPT_OVERRIDE]) {
            $this->output->writeln("<error>File '$path' exists</error>");

            return;
        }
        $origin = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        $ns = explode('\\', $this->options[self::OPT_NAMESPACE]);
        $vendor = array_shift($ns);
        $json = [
            'name' => strtolower($vendor) . '/' . strtolower(implode('-', $ns)),
            'require' => [
                'slim/slim' => '3.5.0',
                'rest-suite/lib' => '~0',
            ],
            'require-dev' => [
                'rest-suite/generator' => '~0',
                'codeception/codeception' => '>=2.2.5 <2.7',
                'fzaninotto/faker' => '1.6.*',
                'flow/jsonpath' => '~0.3',
                'composer/composer' => '1.3.*',
            ],
            'autoload' => [
                'psr-4' => [
                    $this->options[self::OPT_NAMESPACE] . '\\' =>
                        './src/' . str_replace(
                            DIRECTORY_SEPARATOR, '/',
                            $this->getPathFromNamespace($this->options[self::OPT_NAMESPACE])
                        ) . '/'
                ]
            ]
        ];
        $json = array_merge($origin, $json);
        $json['require'] = array_merge($origin['require'], $json['require']);
        $json['require-dev'] = array_merge($origin['require-dev'], $json['require-dev']);
        $json['autoload']['psr-4'] = array_merge($origin['autoload']['psr-4'], $json['autoload']['psr-4']);
        file_put_contents($path, json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        if ($json != $origin) {
            $this->output->writeln("<info>composer.json " . ($origin == [] ? 'created' : 'updated') . "</info>");
        } else {
            $this->output->writeln("composer.json is up to date");
        }
    }
}