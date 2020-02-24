<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Parser\PresetParser;
use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('show')
            ->setDescription('Show available recipes and presets')
            ->addOption('preset', 'p', InputOption::VALUE_OPTIONAL, 'Show recipes included in preset')
            ->addOption('recipe', 'r', InputOption::VALUE_OPTIONAL, 'Show steps included in recipe')
            ->addOption('list-presets', null, InputOption::VALUE_NONE, 'List all presets')
            ->addOption('list-recipes', null, InputOption::VALUE_NONE, 'List all recipes');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \MortenScheel\PhpDependencyInstaller\Parser\ParserException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list-presets')) {
            $files = $this->filesystem->getPresetFiles();
            if ($files === null) {
                $output->writeln('<comment>No presets found</comment>');
            } else {
                $table = new Table($output);
                $table->setStyle('compact')->setHeaders(['Presets', '']);
                foreach ($files as $name => $path) {
                    $table->addRow(["<fg=white>$name</>", $path]);
                }
                $table->render();
            }
        } elseif ($input->getOption('list-recipes')) {
            $table = new Table($output);
            $table->setHeaders(['Recipes', ''])->setStyle('compact');
            $recipes = (new PresetParser())->getRecipes();
            foreach ($recipes as $recipe) {
                $table->addRow(["<fg=white>{$recipe->getName()}</>", $recipe->getDescription()]);
            }
            $table->render();

        } elseif ($recipe = $input->getOption('recipe')) {
            /** @var Recipe $recipe */
            $recipe = (new PresetParser())->getRecipes()->get($recipe);

        }
    }

    private function debug(SymfonyStyle $io, Filesystem $files, InputInterface $input, OutputInterface $output)
    {
        dump((new PresetParser())->getRecipes());
    }

    private function showPreset(?string $preset)
    {
        return 0;
    }
}
