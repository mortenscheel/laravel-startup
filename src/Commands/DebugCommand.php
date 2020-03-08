<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use MortenScheel\PhpDependencyInstaller\Repositories\RecipeRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

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
        $repo = new RecipeRepository();
        $names = $repo->all()->map(function (Recipe $recipe) {
            return $recipe->getName();
        });
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select recipes: ', $names->toArray());
        $question->setMultiselect(true);
        $choice = $helper->ask($input, $output, $question);
        dump($choice);
        return 0;
    }
}
