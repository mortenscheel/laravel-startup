<?php

namespace MortenScheel\LaravelBlitz\Parser;

use MortenScheel\LaravelBlitz\Actions\Action;
use MortenScheel\LaravelBlitz\Actions\ActionCollection;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class YamlConfigParser implements ParserInterface
{
    /**
     * @var string
     */
    private $yaml;

    /** @var array */
    private $actions = [];

    /**
     * YamlConfigParser constructor.
     * @param string $yaml
     */
    public function __construct(string $yaml)
    {
        $this->yaml = $yaml;
    }

    /**
     * @inheritDoc
     */
    public function getActions(): ActionCollection
    {
        try {
            $parsed = Yaml::parse($this->yaml);
            foreach ($parsed as $item) {
                if ($recipe_name = array_get($item, 'recipe')) {
                    $this->parseRecipe($recipe_name);
                } else {
                    $this->parseItem($item);
                }
            }
            return new ActionCollection($this->actions);
        } catch (\Exception $e) {
            throw new ParserException($e->getMessage());
        }
    }

    /**
     * @param string $name
     * @throws ParserException
     */
    private function parseRecipe(string $name)
    {
        $recipe = $this->getRecipe($name);
        foreach ($recipe as $item) {
            $this->parseItem($item);
        }
    }

    /**
     * @param string $name
     * @return array
     * @throws ParserException
     */
    private function getRecipe(string $name): array
    {
        return $this->getRecipes()->get($name);
    }

    /**
     * @return Collection
     * @throws ParserException
     */
    public function getRecipes(): Collection
    {
        $path = __DIR__ . '/../../config/recipes.yml';
        if (!\file_exists($path)) {
            throw new ParserException('No recipes found');
        }
        return collect(Yaml::parseFile($path))->mapWithKeys(function (array $recipe) {
            return [\array_keys($recipe)[0] => \array_values($recipe)[0]];
        });
    }

    /**
     * @param array $item
     * @throws ParserException
     */
    private function parseItem(array $item): void
    {
        $this->actions[] = Action::make($item);
        foreach (array_get($item, 'then', []) as $after) {
            $this->parseItem($after);
        }
    }
}
