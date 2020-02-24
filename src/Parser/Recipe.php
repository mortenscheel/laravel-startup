<?php

namespace MortenScheel\PhpDependencyInstaller\Parser;

use MortenScheel\PhpDependencyInstaller\Actions\Action;
use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Support\Collection;

class Recipe implements Arrayable
{
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string|null
     */
    private $description;
    /**
     * @var string|null
     */
    private $url;
    /**
     * @var Collection|Action[]
     */
    private $actions;

    /**
     * Recipe constructor.
     * @param array $definition
     * @param string|null $name
     */
    public function __construct(array $definition, string $name = null)
    {
        $this->name = $name;
        $this->description = array_get($definition, 'description');
        $this->url = array_get($definition, 'url');
        $this->actions = collect($definition['actions'])->map(function ($action_definition) {
            return Action::make($action_definition);
        });
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'actions' => $this->actions->map(function (Action $action) {
                return $action->getDescription();
            })->toArray()
        ];
    }
}
