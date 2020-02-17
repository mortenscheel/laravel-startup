<?php

namespace MortenScheel\LaravelBlitz\Tests\Transformers;

use MortenScheel\LaravelBlitz\Tests\TransformerTestCase;
use MortenScheel\LaravelBlitz\Transformers\AppendPhpArrayTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AppendPhpArrayTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AppendPhpArrayTransformer($original, '$middleware', '\MyNamespace\MyClass::class');
    }
}
