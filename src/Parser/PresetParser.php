<?php

namespace MortenScheel\PhpDependencyInstaller\Parser;

use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Actions\ActionCollection;
use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\RecipeRepository;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class PresetParser
{
    /**
     * @param string $path
     * @return Collection|Recipe[]
     */
    public static function parseFile(string $path): Collection
    {
        $steps = Yaml::parseFile($path);
        $recipes = new RecipeRepository(new Filesystem());
        return collect($steps)->map(function ($step) use ($recipes) {
            return $recipes->get($step);
        });
    }
}
