<?php

namespace MortenScheel\LaravelBlitz\Commands;

use MortenScheel\LaravelBlitz\Actions\ActionInterface;
use MortenScheel\LaravelBlitz\Actions\ComposerInstall;
use MortenScheel\LaravelBlitz\Concerns\ProcessRunner;
use MortenScheel\LaravelBlitz\Git;
use MortenScheel\LaravelBlitz\Parser\ConfigParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    use ProcessRunner;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Run without asking for confirmation.')
            ->setDescription('Bootstrap a Laravel Application');
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
            $start = \microtime(true);
            $parser = new ConfigParser;
            $actions = $parser->getActions();
            if ($actions->getMigrateCommand() && !$this->canMigrate()) {
                $io->error('Unable to perform migrations. Please check the configuration.');
                return 1;
            }
            $io->writeln(\sprintf('<fg=white>Using %s</>', $parser->resolveConfigPath()));
            $io->write($actions->listActions());
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
            $io->writeln(\sprintf('Blitz completed in %.2f seconds', $seconds));
        } catch (\Exception $e) {
            return 1;
        }
        return 0;
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
     * @param \MortenScheel\LaravelBlitz\Actions\ActionCollection $actions
     * @param bool $dev
     * @return bool
     */
    private function installMultiplePackages(\MortenScheel\LaravelBlitz\Actions\ActionCollection $actions, bool $dev = false)
    {
        $packages = $actions->map(function (ComposerInstall $action) {
            return "{$action->package}={$action->version}";
        })->toArray();
        $command = \array_merge(
            [
                $this->getPhpExecutable(),
                '-n',
                $this->getComposerExecutable(),
                'require',
                '--no-interaction'
            ],
            $packages,
            $dev ? ['--dev', '--no-update'] : []
        );
        return $this->shell($command, true);
    }

    private function canMigrate()
    {
        if ($this->shell([$this->getPhpExecutable(), '-n', 'artisan', 'migrate:status'], true)) {
            return true;
        }
        if (\mb_stripos($this->process_output, 'migration table not found') !== false) {
            return $this->shell([$this->getPhpExecutable(), '-n', 'artisan', 'migrate:install'], true);
        }
        return false;
    }
}
