<?php

namespace MortenScheel\PhpDependencyInstaller\Parser;

use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Actions\ActionCollection;
use MortenScheel\PhpDependencyInstaller\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class ConfigParser
{

    /** @var array */
    private $actions = [];

    /**
     * Parse yaml file with recipes
     * @param string $path
     * @throws ParserException
     */
    public function parseCookbook(string $path)
    {
        try {
            $parsed = Yaml::parseFile((new Filesystem)->getAbsolutePath($path));
            foreach ($parsed as $item) {
                if ($recipe_name = array_get($item, 'recipe')) {
                    $this->parseRecipe($recipe_name);
                } else {
                    $this->parseItem($item);
                }
            }
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
        $recipe = $this->getRecipes()->get($name);
        if (!$recipe) {
            throw new ParserException('Unknown recipe: ' . $name);
        }
        return $recipe;
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
        return  Collection::wrap(Yaml::parseFile($path))->mapWithKeys(function (array $recipe) {
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

    /**
     * @param array $names
     * @throws ParserException
     */
    public function parseRecipes(array $names)
    {
        foreach ($names as $name) {
            $this->parseRecipe($name);
        }
    }

    public function getActions(): ActionCollection
    {
        return (new ActionCollection($this->actions))->unique()->values();
    }
}
