<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

interface ActionInterface
{
    public function getDescription(): string;

    public function execute(): bool;

    public function getError(): ?string;
}
