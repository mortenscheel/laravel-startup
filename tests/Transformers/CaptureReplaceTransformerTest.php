<?php

namespace MortenScheel\LaravelBlitz\Tests\Transformers;

use MortenScheel\LaravelBlitz\Tests\TransformerTestCase;
use MortenScheel\LaravelBlitz\Transformers\CaptureReplaceTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class CaptureReplaceTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new CaptureReplaceTransformer($original, "~'guards'.*'api'.*'driver' => '(\\w+)'~mUs", 'passport');
    }
}
