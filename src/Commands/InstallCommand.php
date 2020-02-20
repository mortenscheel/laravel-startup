<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Actions\ActionInterface;
use MortenScheel\PhpDependencyInstaller\Actions\ComposerInstall;
use MortenScheel\PhpDependencyInstaller\Menu;
use MortenScheel\PhpDependencyInstaller\Concerns\RunsShellCommands;
use MortenScheel\PhpDependencyInstaller\Git;
use MortenScheel\PhpDependencyInstaller\Parser\ConfigParser;
use MortenScheel\PhpDependencyInstaller\Parser\ParserException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends Command
{
    use RunsShellCommands;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('install')
            ->addArgument('recipes', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Recipes to install, comma separated')
            ->addOption('cookbook', null, InputOption::VALUE_OPTIONAL, 'Install recipes from a cookbook')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Install without asking for confirmation.')
            ->setDescription('Install pdi recipes');
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
        try {
            $io = new SymfonyStyle($input, $output);
            $recipes = $input->getArgument('recipes');
            $cookbook = $input->getOption('cookbook');
            if (empty($recipes) && $cookbook === null) {
                $menu = new Menu();
                $result = $menu->open();
                dump($result);
                return 0;
            }

            if (!$input->getOption('force')) {
                $this->offerGitActions($io, $input, $output);
            }
            $start = \microtime(true);
            $parser = new ConfigParser;
            try {
                if ($cookbook) {
                    $parser->parseCookbook($cookbook);
                }
                if ($recipes) {
                    $parser->parseRecipes($recipes);
                }
            } catch (ParserException $e) {
                $io->error($e->getMessage());
                return 1;
            }
            $actions = $parser->getActions();
            if ($actions->getMigrateCommand() && !$this->canMigrate()) {
                $io->error('Unable to perform migrations. Please check the configuration.');
                return 1;
            }
            $table = new Table($output);
            $rows = $actions->map(function (ActionInterface $action, $i) {
                $step = $i + 1;
                return [\sprintf('<fg=white;options=bold>%2d</>', $step), $action->getDescription()];
            })->toArray();
            $table->setHeaders([' #', 'Description'])
                ->setRows($rows)
                ->setStyle('box')
                ->render();
            if (!$input->getOption('force') && !$io->confirm('Execute these action?')) {
                return 0;
            }
            $require_dev = $actions->getInstallablePackages(true);
            if ($require_dev->isNotEmpty()) {
                $success = $this->task(
                    $io,
                    'Installing ' . $require_dev->count() . ' dev packages',
                    function () use ($require_dev) {
                        return $this->installMultiplePackages($require_dev, true);
                    },
                    'installing...'
                );
                if (!$success) {
                    return 1;
                }
            }
            $require = $actions->getInstallablePackages();
            if ($require->isNotEmpty()) {
                $success = $this->task(
                    $io,
                    'Installing ' . $require->count() . ' non-dev packages',
                    function () use ($require) {
                        return $this->installMultiplePackages($require);
                    },
                    'installing...'
                );
                if (!$success) {
                    return 1;
                }
            }
            foreach ($actions->getPostInstallActions() as $action) {
                $success = $this->task($io, $action->getDescription(), function () use ($action) {
                    return $action->execute();
                });
                if (!$success) {
                    $io->error($action->getError());
                    return 1;
                }
            }
            $seconds = \microtime(true) - $start;
            $io->writeln(\sprintf('PDI completed in %.2f seconds', $seconds));
        } catch (\Exception $e) {
            return 1;
        }
        return 0;
    }

    private function offerGitActions(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $git = new Git();
        if ($git->isExecutable()) {
            if (!$git->isRepo() && $io->confirm('Initialize git repository?')) {
                $git->init();
            }
            if ($git->isDirty() && $io->confirm('Add and commit changes?')) {
                /** @var QuestionHelper $helper */
                $helper = $this->getHelper('question');
                $message = $helper->ask($input, $output, new Question("Write a commit message:\n", 'Initial commit'));
                $git->add() && $git->commit($message);
            }
        }
    }

    private function canMigrate()
    {
        if ($this->shell([$this->getExecutable('php'), '-n', 'artisan', 'migrate:status'])) {
            return true;
        }
        if (\mb_stripos($this->process_output, 'migration table not found') !== false) {
            return $this->shell([$this->getExecutable('php'), '-n', 'artisan', 'migrate:install']);
        }
        return false;
    }

    private function task(SymfonyStyle $io, string $title, \Closure $task = null, $loadingText = 'executing...')
    {
        $io->write("$title: <comment>{$loadingText}</comment>");

        if ($task === null) {
            $result = true;
        } else {
            try {
                $result = $task() !== false;
            } catch (\Exception $taskException) {
                $result = false;
            }
        }

        if ($io->isDecorated()) { // Determines if we can use escape sequences
            // Move the cursor to the beginning of the line
            $io->write("\x0D");

            // Erase the line
            $io->write("\x1B[2K");
        } else {
            $io->writeln(''); // Make sure we first close the previous line
        }

        $io->writeln(
            "$title: " . ($result ? '<info>✔</info>' : '<error>failed</error>')
        );
        return $result;
    }

    /**
     * @param \MortenScheel\PhpDependencyInstaller\Actions\ActionCollection $actions
     * @param bool $dev
     * @return bool
     */
    private function installMultiplePackages(\MortenScheel\PhpDependencyInstaller\Actions\ActionCollection $actions, bool $dev = false)
    {
        $packages = $actions->map(function (ComposerInstall $action) {
            return $action->getPackageWithVersion();
        })->toArray();
        $command = \array_merge(
            [
                $this->getExecutable('php'),
                '-n',
                $this->getExecutable('composer'),
                'require',
                '--no-interaction'
            ],
            $packages,
            $dev ? ['--dev', '--no-update'] : []
        );
        return $this->shell($command);
    }
}
