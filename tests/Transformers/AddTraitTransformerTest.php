<?php

namespace MortenScheel\PhpDependencyInstaller\Tests\Transformers;

use MortenScheel\PhpDependencyInstaller\Tests\TransformerTestCase;
use MortenScheel\PhpDependencyInstaller\Transformers\AddTraitTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AddTraitTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AddTraitTransformer($original, 'User', 'MyTrait');
    }
}
