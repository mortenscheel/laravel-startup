<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ConfigCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Customize PHP Dependency Installer configuration')
            ->addOption('show', 's', InputOption::VALUE_NONE, 'Show current configuration')
            ->addOption('copy', null, InputOption::VALUE_NONE, 'Copy global config to current directory')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Debug')
            ->addOption('create', 'c', InputOption::VALUE_NONE, 'Create a blank config file');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $files = new Filesystem();
        if ($input->getOption('create')) {
            if ($path = $files->createConfig()) {
                $io->success('Config created at ' . $path);
                return 0;
            }

            $io->comment('Config file already exists');
            return 1;
        }
        if (!$files->hasGlobalConfig()) {
            $io->error('No config file found. Create one with the --create option.');
            return 1;
        }
        if ($input->getOption('show')) {
            $io->write($files->getConfig());
            return 0;
        }
        if ($input->getOption('debug')) {
            $this->debug($io, $files, $input, $output);
            return 0;
        }
        if ($input->getOption('copy')) {
            $files->copy($files->getGlobalConfigFilePath(), \getcwd() . '/pdi.yml');
            $io->success('Config file copied');
            return 0;
        }
        return (new Process(['open', $files->getGlobalConfigFilePath()]))->run();
    }

    private function debug(SymfonyStyle $io, Filesystem $files, InputInterface $input, OutputInterface $output)
    {
    }
}
