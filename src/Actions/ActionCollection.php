<?php

namespace MortenScheel\LaravelBlitz\Actions;

use Tightenco\Collect\Support\Collection;

class ActionCollection extends Collection
{
    public function listActions(): string
    {
        $width = \mb_strlen($this->count());
        return $this->map(function (ActionInterface $action, $index) use ($width) {
            return \sprintf("<info>%{$width}d</info> %s", $index + 1, $action->getDescription());
        })->join(\PHP_EOL);
    }
}
