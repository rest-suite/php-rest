<?php

namespace bc\rest\commands;

use bc\rest\gen\Generator;
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
        
        $ns = $input->getOption('namespace');

        $gen = new Generator($swaggerFile, $ns);
        
        $gen->getPaths();
    }

}