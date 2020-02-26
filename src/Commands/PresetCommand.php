<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\ActionManager;
use MortenScheel\PhpDependencyInstaller\Git;
use MortenScheel\PhpDependencyInstaller\Repositories\PresetRepository;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PresetCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('preset')
            ->setAliases(['presets'])
            ->addArgument('preset', InputArgument::OPTIONAL, 'Preset to install')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List available presets')
            ->addOption('no-optimize', null, InputOption::VALUE_NONE, 'Do not optimize the order of actions')
            ->addOption('skip-git-check', null, InputOption::VALUE_NONE, 'Allow running without a clean git repository')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Combination of --no-interaction and --skip-git-check')
            ->setDescription('Install recipes from presets');
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
        $repo = new PresetRepository();
        if ($input->getOption('list')) {
            $table = new Table($output);
            $table->setStyle('box')
                ->setHeaders(['Preset', 'Recipes']);
            foreach ($repo->all() as $index => $preset) {
                if ($index !== 0) {
                    $table->addRow(new TableSeparator());
                }
                $recipes = $preset->getRecipes();
                $recipe_names = $recipes->map(function ($recipe) {
                    return $recipe->getName();
                })->join("\n");
                $table->addRow(["<fg=white>{$preset->getName()}</>", $recipe_names]);
            }
            $table->render();
            return 0;
        }
        if (!$input->getOption('force') && !$input->getOption('skip-git-check') && !$this->ensureCleanGitRepo($output)) {
            return 1;
        }
        $preset_name = $input->getArgument('preset');
        if (!$preset_name) {
            $output->writeln('<error>Preset name missing</error>');
            return 1;
        }
        $preset = $repo->get($preset_name);
        if (!$preset) {
            $output->writeln("<error>Preset $preset_name not found</error>");
            return 1;
        }
        $manager = new ActionManager();
        $manager->setRecipes($preset->getRecipes());
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
