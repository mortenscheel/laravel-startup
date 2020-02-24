<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Parser\PresetParser;
use MortenScheel\PhpDependencyInstaller\RecipeRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('debug')
            ->setDescription('Command for ad.hoc debugging during development');
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
        $repo = new RecipeRepository(new Filesystem());
        dump($repo->all()->toArray());
        return 0;
    }
}
