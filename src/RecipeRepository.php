<?php


namespace MortenScheel\PhpDependencyInstaller;


use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class RecipeRepository
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * RecipeManager constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function all(): Collection
    {
        return $this->getRecipeDefinitions()->map(function ($definition, $name) {
            return new Recipe($definition, $name);
        });
    }

    public function get(string $name): ?Recipe
    {
        if ($definition = $this->getRecipeDefinition($name)) {
            return new Recipe($definition, $name);
        }
        return null;
    }

    protected function getRecipeDefinition(string $name): ?array
    {
        return $this->getRecipeDefinitions()->get($name);
    }

    public function getRecipeDefinitions(): Collection
    {
        $default = $this->getDefaultRecipeDefinitions();
        $custom = $this->getCustomRecipeDefinitions();
        return collect(array_merge($default, $custom));
    }

    protected function getDefaultRecipeDefinitions(): array
    {
        $path = __DIR__ . '/../config/recipes.yml';
        return Yaml::parseFile($path);
    }

    protected function getCustomRecipeDefinitions(): array
    {
        $path = $this->filesystem->getGlobalConfigPath('recipes.yml');
        if ($this->filesystem->exists($path)) {
            return Yaml::parseFile($path);
        }
        return [];
    }

}
