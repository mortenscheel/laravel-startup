<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\ActionManager;
use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Git;
use MortenScheel\PhpDependencyInstaller\Menu;
use MortenScheel\PhpDependencyInstaller\Parser\Preset;
use MortenScheel\PhpDependencyInstaller\Repositories\RecipeRepository;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MenuCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('menu')
            ->setDescription('Text User Interface');
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
        $menu = new Menu();
        $selection = $menu->open();
        return 0;
    }
}
