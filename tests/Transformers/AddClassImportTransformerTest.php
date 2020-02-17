<?php

namespace MortenScheel\LaravelBlitz\Tests\Transformers;

use MortenScheel\LaravelBlitz\Tests\TransformerTestCase;
use MortenScheel\LaravelBlitz\Transformers\AddClassImportTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AddClassImportTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AddClassImportTransformer($original, 'User', 'MyNamespace\MyClass');
    }
}
