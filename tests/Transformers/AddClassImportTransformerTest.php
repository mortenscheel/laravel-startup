<?php

namespace MortenScheel\PhpDependencyInstaller\Tests\Transformers;

use MortenScheel\PhpDependencyInstaller\Tests\TransformerTestCase;
use MortenScheel\PhpDependencyInstaller\Transformers\AddClassImportTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AddClassImportTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AddClassImportTransformer($original, 'User', 'MyNamespace\MyClass');
    }
}
