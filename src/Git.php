<?php

namespace MortenScheel\PhpDependencyInstaller;

class Git
{
    public const STATUS_NOT_INSTALLED = 'not-installed';
    public const STATUS_NOT_INITIALIZED = 'not-initialized';
    public const STATUS_CLEAN = 'clean';
    public const STATUS_DIRTY = 'dirty';
    /**
     * @var Shell
     */
    private $shell;

    /**
     * Git constructor.
     */
    public function __construct()
    {
        $this->shell = new Shell();
    }

    public function getStatus()
    {
        if (!$this->isExecutable()) {
            return self::STATUS_NOT_INSTALLED;
        }
        if (!$this->isRepo()) {
            return self::STATUS_NOT_INITIALIZED;
        }
        if ($this->isDirty()) {
            return self::STATUS_DIRTY;
        }
        return self::STATUS_CLEAN;
    }

    protected function isExecutable()
    {
        return $this->shell->execute(['git', '--version']) === true;
    }

    protected function isRepo()
    {
        $files = new Filesystem();
        return $files->exists($files->getAbsolutePath('.git'));
    }

    protected function isDirty()
    {
        $this->shell->execute(['git', 'status', '--short']);
        return $this->shell->flushOutput() !== '';
    }

}
