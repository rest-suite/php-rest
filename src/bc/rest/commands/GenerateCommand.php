<?php

namespace bc\rest\commands;

use bc\rest\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command {

    const ARG_SPEC = 'spec';

    protected function configure() {
        $this->setName('generate')
             ->setAliases(['gen', 'g'])
             ->addArgument(self::ARG_SPEC, InputArgument::OPTIONAL, "path to swagger specs", "swagger.yml")
             ->addOption(Builder::OPT_NAMESPACE, 'ns', InputOption::VALUE_REQUIRED, "base namespace for generated code")
             ->addOption(Builder::OPT_OUTPUT, 'o', InputOption::VALUE_OPTIONAL, "output path for generated code", './')
             ->addOption(Builder::OPT_MODELS, 'm', InputOption::VALUE_NONE, "only generate files for models")
             ->addOption(Builder::OPT_CONTROLLERS, 'c', InputOption::VALUE_NONE, "only generate files for controllers")
             ->addOption(Builder::OPT_TESTS, 't', InputOption::VALUE_NONE, "only generate files for tests")
             ->addOption(Builder::OPT_SETTINGS, 's', InputOption::VALUE_NONE, "only generate dist settings files")
             ->addOption(Builder::OPT_OVERRIDE, null, InputOption::VALUE_NONE, "override existing files");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $swaggerFile = $input->getArgument(self::ARG_SPEC);
        if(!file_exists($swaggerFile) || !is_readable($swaggerFile)) {
            throw new InvalidArgumentException(sprintf("Specs not found: %s", $swaggerFile));
        }

        $outputPath = $input->getOption(Builder::OPT_OUTPUT);
        if(!file_exists($outputPath) || !is_dir($outputPath)) {
            throw new InvalidArgumentException(sprintf("Output path not found: %s", $outputPath));
        }
        $outputPath = rtrim($outputPath, DIRECTORY_SEPARATOR);

        $optModels = $input->getOption(Builder::OPT_MODELS);
        $optControllers = $input->getOption(Builder::OPT_CONTROLLERS);
        $optSettings = $input->getOption(Builder::OPT_SETTINGS);
        $optTests = $input->getOption(Builder::OPT_TESTS);

        $options = [
            Builder::OPT_MODELS      => $optModels,
            Builder::OPT_CONTROLLERS => $optControllers,
            Builder::OPT_SETTINGS    => $optSettings,
            Builder::OPT_TESTS       => $optTests,
            Builder::OPT_ALL         => !($optModels | $optControllers | $optSettings | $optTests),
            Builder::OPT_NAMESPACE   => $input->getOption(Builder::OPT_NAMESPACE),
            Builder::OPT_OVERRIDE    => $input->getOption(Builder::OPT_OVERRIDE),
            Builder::OPT_OUTPUT_PATH => $outputPath,
            Builder::OPT_SWAGGER     => $swaggerFile
        ];

        $builder = new Builder($options, $output);
        $builder->build();
    }

}