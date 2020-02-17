<?php

namespace MortenScheel\PhpDependencyInstaller\Tests\Transformers;

use MortenScheel\PhpDependencyInstaller\Tests\TransformerTestCase;
use MortenScheel\PhpDependencyInstaller\Transformers\AppendPhpMethodTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AppendPhpMethodTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AppendPhpMethodTransformer($original, 'boot', '\Passport::install();');
    }
}
