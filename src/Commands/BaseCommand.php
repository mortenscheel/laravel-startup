<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Shell;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * BaseCommand constructor.
     */
    public function __construct()
    {
        parent::__construct(null);
        $this->filesystem = new Filesystem();
        $this->shell = new Shell();
    }
}
