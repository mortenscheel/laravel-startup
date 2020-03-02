<?php


namespace MortenScheel\PhpDependencyInstaller\Repositories;


use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Parser\Preset;
use MortenScheel\PhpDependencyInstaller\Parser\PresetParser;
use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class PresetRepository
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function get(string $name): ?Preset
    {
        $path = $this->getPresetsPath($name);
        if (!$this->filesystem->exists($path)) {
            return null;
        }
        return PresetParser::parseFile($path);
    }

    /**
     * @return Collection|Preset[]
     */
    public function all(): Collection
    {
        return $this->getPresetFiles()->map(function (string $path) {
            return PresetParser::parseFile($path);
        });
    }

    protected function getPresetFiles()
    {
        if ($this->filesystem->exists($this->getPresetsPath())) {
            return collect(scandir($this->getPresetsPath()))->filter(function ($path) {
                return preg_match('/\.yml$/', $path);
            })->map(function ($path) {
                return $this->getPresetsPath() . DIRECTORY_SEPARATOR . $path;
            })->values();
        }
        return collect();
    }

    protected function getPresetsPath(string $preset_name = null)
    {
        $path = $this->filesystem->getGlobalConfigPath('presets');
        if (!$preset_name) {
            return $path;
        }
        return sprintf('%s%s%s.yml', $path, DIRECTORY_SEPARATOR, $preset_name);
    }

    public function save(Preset $preset): bool
    {
        $name = $preset->getName();
        $recipe_names = $preset->getRecipes()->map(function (Recipe $recipe) {
            return $recipe->getName();
        })->toArray();
        $yaml = Yaml::dump($recipe_names);
        $folder = $this->getPresetsPath();
        if (!$this->filesystem->exists($folder)) {
            $this->filesystem->mkdir($folder, 0755);
        }
        $path = $this->getPresetsPath($name);
        if ($this->filesystem->exists($path)) {
            return false;
        }
        $this->filesystem->put($path, $yaml);
        return true;
    }
}
