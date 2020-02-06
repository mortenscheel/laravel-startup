<?php


namespace MortenScheel\LaravelBlitz\Parser;


use MortenScheel\LaravelBlitz\Actions\ActionCollection;
use MortenScheel\LaravelBlitz\Actions\ActionInterface;

interface ParserInterface
{
    /**
     * @return ActionCollection|ActionInterface[]
     * @throws ParserException
     */
    public function getActions(): ActionCollection;
}
