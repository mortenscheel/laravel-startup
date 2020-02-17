<?php

namespace MortenScheel\LaravelBlitz\Tests\Transformers;

use MortenScheel\LaravelBlitz\Tests\TransformerTestCase;
use MortenScheel\LaravelBlitz\Transformers\AppendPhpMethodTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AppendPhpMethodTransformerTest extends TransformerTestCase
{

    public function getTestTransformer(string $original): Transformer
    {
        return new AppendPhpMethodTransformer($original, 'boot', '\Passport::install();');
    }
}
