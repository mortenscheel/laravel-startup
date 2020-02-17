<?php

namespace MortenScheel\LaravelBlitz\Tests;

use MortenScheel\LaravelBlitz\Transformers\Transformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class TransformerTestCase extends TestCase
{
    /**
     * @var array
     */
    protected $originals;
    /**
     * @var array
     */
    protected $expected;

    public function testIsTransformationRequired()
    {
        foreach ($this->originals as $name => $original) {
            $expected = $this->expected[$name];
            $required = $this->getTestTransformer($original);
            $required_actual = $required->isTransformationRequired();
            // Original should require transformation
            $this->assertTrue($required_actual, $name);
            // Transformed should not require transformation
            $not_required = $this->getTestTransformer($expected);
            $not_required_actual = $not_required->isTransformationRequired();
            $this->assertFalse($not_required_actual, $name);
        }
    }

    abstract public function getTestTransformer(string $original): Transformer;

    public function testTransform()
    {
        foreach ($this->originals as $name => $original) {
            $expected = $this->expected[$name];
            $transformer = $this->getTestTransformer($original);
            $actual = $transformer->transform();
            $this->assertEquals($actual, $expected, $name);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $names = \explode('\\', \get_class($this));
        $class_name = \array_pop($names);
        $folder = \sprintf('%s/Fixtures/%s', __DIR__, $class_name);
        if (!\file_exists($folder)) {
            throw new \RuntimeException('Fixtures folder not found for ' . $class_name);
        }
        /** @var SplFileInfo $item */
        foreach (Finder::create()->in($folder)->directories() as $case_folder) {
            $original_path = $case_folder->getPathname() . '/original.txt';
            $expected_path = $case_folder->getPathname() . '/expected.txt';
            if (!\file_exists($original_path) || !\file_exists($expected_path)) {
                throw new \RuntimeException('Testcase files missing');
            }
            $this->originals[\sprintf('%s: %s', $class_name, $case_folder->getRelativePathname())] = \file_get_contents($original_path);
            $this->expected[\sprintf('%s: %s', $class_name, $case_folder->getRelativePathname())] = \file_get_contents($expected_path);
        }
    }
}
