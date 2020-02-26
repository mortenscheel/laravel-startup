<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use Symfony\Component\Process\Process;

interface AsyncAction
{
    public function getProcess(): Process;
}
