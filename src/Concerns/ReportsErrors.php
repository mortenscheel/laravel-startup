<?php

namespace MortenScheel\PhpDependencyInstaller\Concerns;

trait ReportsErrors
{
    protected $error;

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
