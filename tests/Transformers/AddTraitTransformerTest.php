<?php

namespace MortenScheel\LaravelBlitz\Tests\Transformers;

use MortenScheel\LaravelBlitz\Tests\TransformerTestCase;
use MortenScheel\LaravelBlitz\Transformers\AddTraitTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AddTraitTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new AddTraitTransformer($original, 'User', 'MyTrait');
    }
}
