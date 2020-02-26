<?php

namespace MortenScheel\PhpDependencyInstaller;

use AlecRabbit\Spinner\Core\Adapters\SymfonyOutputAdapter;
use AlecRabbit\Spinner\SnakeSpinner;
use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Actions\AsyncAction;
use Symfony\Component\Console\Output\OutputInterface;

class ActionRunner
{
    /**
     * @var Action
     */
    private $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function run(OutputInterface $output, bool $verbose = false): bool
    {
        $output_adapter = new SymfonyOutputAdapter($output);
        $description = sprintf('<fg=white>%s</>', $this->action->getDescription());
        $spinner = new SnakeSpinner($description, $output_adapter);
        $spinner->begin();
        if ($this->action instanceof AsyncAction) {
            $process = $this->action->getProcess();
            if ($output->isDecorated()) {
                $process->setPty(true);
            }
            $process->start();
            while ($process->isRunning()) {
                usleep(80000);
                if ($verbose) {
                    $stdout = $process->getIncrementalOutput();
                    $stderr = $process->getIncrementalErrorOutput();
                    if ($stdout) {
                        $output->writeln($stdout);
                    }
                    if ($stderr) {
                        $output->writeln($stderr);
                    }
                }
                $spinner->spin();
            }
            $success = $process->isSuccessful();
            $error = sprintf("%s\n%s", $process->getOutput(), $process->getErrorOutput());
        } else {
            $success = $this->action->execute();
            $error = $this->action->getError();
        }
        if (!$success && !$verbose) {
            $output->writeln($error);
        }
        $icon = $success ? '<fg=green>✓</>' : '<fg=red>✗</>';
        $spinner->end("$icon $description\n");
        return $success;
    }
}
