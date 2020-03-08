<?php

namespace MortenScheel\PhpDependencyInstaller\Parser;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Repositories\RecipeRepository;
use Symfony\Component\Yaml\Yaml;

class PresetParser
{
    /**
     * @param string $path
     * @return Preset
     */
    public static function parseFile(string $path): Preset
    {
        $name = \pathinfo($path, \PATHINFO_FILENAME);
        $preset_recipes = Yaml::parseFile($path);
        $recipes_repository = new RecipeRepository(new Filesystem());
        $recipes = collect($preset_recipes)->map(function ($preset_recipe) use ($recipes_repository) {
            return $recipes_repository->get($preset_recipe, true);
        });
        return new Preset($name, $recipes);
    }
}
