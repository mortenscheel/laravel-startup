<?php


namespace MortenScheel\PhpDependencyInstaller\Parser;


use MortenScheel\PhpDependencyInstaller\Repositories\PresetRepository;
use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Support\Collection;

class Preset implements Arrayable
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Collection
     */
    private $recipes;

    /**
     * Preset constructor.
     * @param string $name
     * @param Collection $recipes
     */
    public function __construct(string $name, Collection $recipes)
    {
        $this->name = $name;
        $this->recipes = $recipes;
    }

    public function save(): bool
    {
        $repository = new PresetRepository();
        return $repository->save($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'recipes' => $this->recipes->toArray()
        ];
    }
}
