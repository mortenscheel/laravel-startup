<?php

namespace MortenScheel\LaravelStartup\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class InstallPhpCsFixerCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-tools:install-cs-fixer {--yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install php-cs-fixer and publish .php_cs config';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        if (!\class_exists('PhpCsFixer\Config')) {
            $this->info('Installing php-cs-fixer');
            if (!$this->runProcess(['composer', 'require', 'friendsofphp/php-cs-fixer'])) {
                $this->error('Installation failed');
                return 1;
            }
        }
        try {
            $stub = \File::get(__DIR__ . '/../../stubs/.php_cs-laravel.php');
            $destination = base_path('.php_cs');
            if (!\File::exists($destination) ||
                $this->option('yes') ||
                $this->confirm('.php_cs file already exists. Overwrite?')) {
                \File::put($destination, $stub);
                $this->info('.php_cs was generated');
            }
            if ($this->option('yes') ||
                $this->confirm('Update PhpStorm inspection profiles?')) {
                $this->updateInspectionProfiles();
            }
            return 0;
        } catch (FileNotFoundException $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function updateInspectionProfiles()
    {
        $finder = Finder::create()
            ->in(base_path('.idea/InspectionProfiles'))
            ->name('*.xml')
            ->files();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $dom = new \DOMDocument();
            $dom->load($file->getPathname());
            $inspection_nodes = $dom->getElementsByTagName('inspection_tool');
            /** @var \DOMElement $target_node */
            $target_node = null;
            /** @var \DOMNode $inspection_node */
            foreach ($inspection_nodes as $inspection_node) {
                $attributes = $inspection_node->attributes;
                /** @var \DOMAttr $class */
                $class = $attributes->getNamedItem('class');
                if ($class->value === 'PhpCSFixerValidationInspection') {
                    $target_node = $inspection_node;
                    break;
                }
            }
            if (!$target_node) {
                /** @var \DOMNode $parent */
                $parent = $dom->getElementsByTagName('profile')->item(0);
                $target_node = $dom->createElement('inspection_tool');
                $parent->appendChild($target_node);
            }
            $attributes = [
                'class' =>   'PhpCSFixerValidationInspection',
                'enabled' => 'true',
                'level' => 'WEAK WARNING',
                'enabled_by_default' => 'true'
            ];
            foreach ($attributes as $name => $value) {
                $target_node->setAttribute($name, $value);
            }
            if ($target_node->hasChildNodes()) {
                foreach ($target_node->childNodes as $childNode) {
                    $target_node->removeChild($childNode);
                }
            }
            $options = [
                'CODING_STANDARD' => 'Custom',
                'CUSTOM_RULESET_PATH' => '$PROJECT_DIR$/.php_cs',
                'ALLOW_RISKY_RULES' => 'true'
            ];
            foreach ($options as $name => $value) {
                /** @var \DOMElement $option */
                $option = $dom->createElement('option');
                $option->setAttribute('name', $name);
                $option->setAttribute('value', $value);
                $target_node->appendChild($option);
            }
            $dom->save($file->getPathname());
        }
    }
}
