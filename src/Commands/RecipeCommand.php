<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\ActionManager;
use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Git;
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

class RecipeCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('recipe')
            ->setAliases(['recipes'])
            ->addArgument('recipes', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Recipes to install, comma separated')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List available recipes')
            ->addOption('edit', 'e', InputOption::VALUE_NONE, 'Open custom recipes in text editor')
            ->addOption('save', 's', InputOption::VALUE_OPTIONAL, 'Save as preset with the give name')
            ->addOption('no-optimize', null, InputOption::VALUE_NONE, 'Do not optimize the order of actions')
            ->addOption('skip-git-check', null, InputOption::VALUE_NONE, 'Allow running without a clean git repository')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Combination of --no-interaction and --skip-git-check')
            ->setDescription('Install recipes');
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
        $manager = new ActionManager();
        if ($input->getOption('list')) {
            $table = new Table($output);
            $table->setStyle('box')
                ->setColumnMaxWidth(1, 50)
                ->setColumnMaxWidth(2, 60)
                ->setHeaders(['Name', 'Description', 'Actions']);
            foreach ($repo->all() as $index => $recipe) {
                if ($index !== 0) {
                    $table->addRow(new TableSeparator());
                }
                $table->addRow([
                    "<fg=white>{$recipe->getName()}</>",
                    $recipe->getDescription(),
                    $recipe->getActions()->map(function (Action $action) {
                        return $action->getDescription();
                    })->join("\n\n")
                ]);
            }
            $table->render();
            return 0;
        }
        if ($input->getOption('edit')) {
            $path = $repo->getCustomRecipeConfigurationPath(true);
            return $this->shell->execute(['open', $path]) ? 0 : 1;
        }
        $recipe_names = $input->getArgument('recipes');
        if (empty($recipe_names)) {
            $output->writeln('<error>Expected one or more recipes to install</error>');
            return 1;
        }
        foreach ($recipe_names as $recipe_name) {
            $recipe = $repo->get($recipe_name, true);
            if (!$recipe) {
                $output->write("<error>$recipe_name was not recognized as a recipe</error>");
                return 1;
            }
            $manager->addRecipe($recipe);
        }
        if ($name = $input->getOption('save')) {
            $preset = new Preset($name, $manager->getRecipes());
            $saved = $preset->save();
            if ($saved) {
                $output->writeln("<info>Saved as preset: $name</info>");
            } else {
                $output->writeln("Another preset called $name exists.");
            }
        }
        if (!$input->getOption('force') && !$input->getOption('skip-git-check') && !$this->ensureCleanGitRepo($output)) {
            return 1;
        }
        $optimize = !$input->getOption('no-optimize');
        if (!$input->getOption('no-interaction') &&
            !$input->getOption('force')) {
            $manager->showActionsTable($output, $optimize);
            $question = new ConfirmationQuestion("Do you want to perform these actions?\n");
            $helper = new QuestionHelper();
            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }
        return $manager->execute($output, $optimize, $input->getOption('verbose'));
    }

    private function ensureCleanGitRepo(OutputInterface $output)
    {
        switch ((new Git())->getStatus()) {
            case Git::STATUS_CLEAN:
            case Git::STATUS_NOT_INSTALLED:
                return true;
            case Git::STATUS_NOT_INITIALIZED:
                $error = <<<ERROR
<fg=red>No git repository found.
This command modifies your project files. To ensure that you can undo
the modifications made, a git repository must be initialzed and all
changes committed before running the command.</>
ERROR;
                break;

            case Git::STATUS_DIRTY:
                $error = <<<ERROR
<fg=red>The current working directory contains uncommitted changes.
This command modifies your project files. To ensure that you can undo
the modifications made, the working directory must be clean 
before running the command.</>
ERROR;
                break;
            default:
                return false;
        }
        $error .= "\n<fg=white>To ignore this warning, use the --skip-git-check or --force options.</>";
        $output->writeln($error);
        return false;
    }
}
