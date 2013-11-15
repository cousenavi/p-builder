<?php

namespace Builder\Command;

use Composer\Command\Helper\DialogHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command {

    private $options = [];

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Greet someone');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $projectName = strtolower($dialog->ask($output, 'Enter project name: '));

        while (file_exists($dirName = $this->options['projectFolder'].$projectName)) {
            $projectName = strtolower(
                $dialog->ask($output, "Project with name '$projectName' already exists. Enter another one: ")
            );
        }

        $this->options['projectName'] = $projectName;
        $this->options['bundleName'] = ucfirst($projectName);

        mkdir($dirName);
        $output->writeln('created '.$dirName);

        chdir($dirName);
        $this->createComposerFile();

        $output->writeln(exec("Composer file created. Executing "));
        $output->writeln(exec("composer install"));

    }

    private function createComposerFile()
    {

        $template = <<<TPL
{
    "name": "{$this->options['vendorName']}/{$this->options['projectName']}",
    "minimum-stability": "stable",
    "autoload": {
        "psr-0": {
            "{$this->options['bundleName']}\\\": "src/"
        }
    }
    "require": {
        "symfony/console": "2.3.x-dev"
    },
}
TPL;

        file_put_contents('composer.json', $template);

    }
} 