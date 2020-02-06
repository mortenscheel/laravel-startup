<?php

namespace MortenScheel\LaravelBlitz\Actions;

use Illuminate\Contracts\Container\BindingResolutionException;
use MortenScheel\LaravelBlitz\Console\ConsoleOutput;

abstract class BaseAction
{
    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function __construct(array $attributes = [])
    {
        try {
            $this->output = app()->make(ConsoleOutput::class);
        } catch (BindingResolutionException $e) {
        }
    }

    abstract public function handle();
}
