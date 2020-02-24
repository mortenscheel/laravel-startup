<?php


namespace MortenScheel\PhpDependencyInstaller\Commands;


use MortenScheel\PhpDependencyInstaller\Concerns\RunsShellCommands;
use MortenScheel\PhpDependencyInstaller\Filesystem;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    use RunsShellCommands;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct(null);
        $this->filesystem = $filesystem;
    }
}
