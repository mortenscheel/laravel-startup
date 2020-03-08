<?php

namespace MortenScheel\PhpDependencyInstaller\Repositories;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class RecipeRepository
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @return Collection|Recipe[]
     */
    public function all(): Collection
    {
        return $this->getRecipeDefinitions()->map(function ($definition) {
            return new Recipe($definition);
        })->values();
    }

    public function getRecipeDefinitions(): Collection
    {
        $default = $this->getDefaultRecipeDefinitions();
        $custom = $this->getCustomRecipeDefinitions();
        return $default->merge($custom);
    }

    public function getCustomRecipeConfigurationPath(bool $create_if_missing = false)
    {
        $path = $this->filesystem->getGlobalConfigPath('recipes.yml');
        if ($create_if_missing) {
            $folder = $this->filesystem->getGlobalConfigPath();
            if (!$this->filesystem->exists($folder)) {
                $this->filesystem->mkdir($folder, 0755);
            }
            if (!$this->filesystem->exists($path)) {
                $template = PDI_ROOT . '/config/recipes-template.yml';
                $this->filesystem->copy($template, $path);
            }
        }
        return $path;
    }

    protected function getDefaultRecipeDefinitions(): Collection
    {
        $path = __DIR__ . '/../../config/recipes.yml';
        return collect(Yaml::parseFile($path))->map(function (array $definition, string $name) {
            $definition['name'] = $name;
            return $definition;
        });
    }

    protected function getCustomRecipeDefinitions(): Collection
    {
        $path = $this->getCustomRecipeConfigurationPath();
        if ($this->filesystem->exists($path)) {
            return collect(Yaml::parseFile($path))->map(function (array $definition, string $name) {
                $definition['name'] = $name;
                return $definition;
            });
        }
        return collect();
    }

    public function get(string $name, bool $allow_aliases = false): ?Recipe
    {
        if ($definition = $this->getRecipeDefinition($name, $allow_aliases)) {
            return new Recipe($definition);
        }
        return null;
    }

    protected function getRecipeDefinition(string $name, bool $allow_aliases = false): ?array
    {
        return $this->getRecipeDefinitions()
            ->first(function (array $definition, string $recipe_name) use ($name, $allow_aliases) {
                return $recipe_name === $name || ($allow_aliases && array_get($definition, 'alias') === $name);
            });
    }
}
