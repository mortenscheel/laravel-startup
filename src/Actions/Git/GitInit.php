<?php


namespace MortenScheel\PhpDependencyInstaller\Actions\Git;


use MortenScheel\PhpDependencyInstaller\Actions\Action;

class GitInit extends Action
{

    public function getDescription(): string
    {
        return 'Ensures a git repository is initialized in the project folder';
    }

    public function execute(): bool
    {
        return true;
    }
}
