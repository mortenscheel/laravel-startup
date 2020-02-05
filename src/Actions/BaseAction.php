<?php

namespace MortenScheel\LaravelStartup\Actions;

use Illuminate\Contracts\Container\BindingResolutionException;
use Lorisleiva\Actions\Action;
use MortenScheel\LaravelStartup\Console\ConsoleOutput;

abstract class BaseAction extends Action
{
    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        try {
            $this->output = app()->make(ConsoleOutput::class);
        } catch (BindingResolutionException $e) {
        }
    }

    abstract public function rules(): array;

    abstract public function handle();
}
