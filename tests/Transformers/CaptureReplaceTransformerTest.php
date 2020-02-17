<?php

namespace MortenScheel\PhpDependencyInstaller\Tests\Transformers;

use MortenScheel\PhpDependencyInstaller\Tests\TransformerTestCase;
use MortenScheel\PhpDependencyInstaller\Transformers\CaptureReplaceTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class CaptureReplaceTransformerTest extends TransformerTestCase
{
    public function getTestTransformer(string $original): Transformer
    {
        return new CaptureReplaceTransformer($original, "~'guards'.*'api'.*'driver' => '(\\w+)'~mUs", 'passport');
    }
}
