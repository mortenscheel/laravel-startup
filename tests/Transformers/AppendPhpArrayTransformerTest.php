<?php

namespace MortenScheel\PhpDependencyInstaller\Tests\Transformers;

use MortenScheel\PhpDependencyInstaller\Tests\TransformerTestCase;
use MortenScheel\PhpDependencyInstaller\Transformers\AppendPhpArrayTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AppendPhpArrayTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AppendPhpArrayTransformer($original, '$middleware', '\MyNamespace\MyClass::class');
    }
}
