<?php

namespace bc\rest\commands;

use bc\rest\gen\ClassesGenerator;
use gossi\codegen\generator\CodeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAll extends Command {

    protected function configure() {
        $this->setName('generate')
             ->setAliases(['gen', 'g'])
             ->addArgument("spec", InputArgument::OPTIONAL, "path to swagger specs", "swagger.yml")
             ->addOption('namespace', 'ns', InputOption::VALUE_REQUIRED, 'base namespace for generated code')
             ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'output path for generated code', './');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $swaggerFile = $input->getArgument("spec");
        if(!file_exists($swaggerFile) || !is_readable($swaggerFile)) {
            throw new InvalidArgumentException(sprintf('Specs not found: %s', $swaggerFile));
        }

        $outputPath = $input->getOption("output");
        if(!file_exists($outputPath) || !is_dir($outputPath)) {
            throw new InvalidArgumentException(sprintf('Output path not found: %s', $outputPath));
        }
        $outputPath = rtrim($outputPath, DIRECTORY_SEPARATOR);

        $ns = $input->getOption('namespace');

        $classes = new ClassesGenerator($swaggerFile, $ns);

        $gen = new CodeGenerator(['generateEmptyDocblock' => false]);

        $bootstrapPath = $outputPath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR
                         .str_replace('\\', DIRECTORY_SEPARATOR, $classes->getBootstrap()->getNamespace());

        $oldumask = umask(0);

        if(!file_exists($bootstrapPath)) {
            mkdir($bootstrapPath, 0755, true);
        }

        file_put_contents($bootstrapPath.DIRECTORY_SEPARATOR.$classes->getBootstrap()->getName().'.php',
                          "<?php\n\n".$gen->generate($classes->getBootstrap()));
        $output->writeln("Bootstrap created");

        foreach($classes->getModels() as $model) {
            $path = $outputPath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR
                    .str_replace('\\', DIRECTORY_SEPARATOR, $model->getNamespace());
            if(!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            file_put_contents($path.DIRECTORY_SEPARATOR.$model->getName().'.php',
                              "<?php\n\n".$gen->generate($model));
            $output->writeln('Model '.$model->getName()." created");
        }

        foreach($classes->getControllers() as $controller) {
            $path = $outputPath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR
                    .str_replace('\\', DIRECTORY_SEPARATOR, $controller->getNamespace());
            if(!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            file_put_contents($path.DIRECTORY_SEPARATOR.$controller->getName().'.php',
                              "<?php\n\n".$gen->generate($controller));
            $output->writeln($controller->getName()." created");
        }

        $configPath = $outputPath.DIRECTORY_SEPARATOR.'config';
        if(!file_exists($configPath)) {
            mkdir($configPath, 0755, true);
        }

        $configs = $classes->getConfigs();
        $fileName = $configPath.DIRECTORY_SEPARATOR.'api.php.dist';
        file_put_contents($fileName, "<?php \n\n return ".var_export($configs, true).';');
        $output->writeln("Config created");

        umask($oldumask);

    }

}