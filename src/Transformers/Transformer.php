<?php


namespace MortenScheel\PhpDependencyInstaller\Transformers;


interface Transformer
{
    public function transform(): ?string;

    public function isTransformationRequired(): bool;

    public function getError(): ?string;
}
