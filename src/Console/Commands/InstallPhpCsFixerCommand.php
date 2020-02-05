<?php

namespace MortenScheel\LaravelStartup\Console\Commands;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class InstallPhpCsFixerCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'startup:cs-fixer {--update-phpstorm-config}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install php-cs-fixer and publish .php_cs config';

    protected $hidden = true;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Installing php-cs-fixer');
//        if (!$this->runProcess(['composer', 'require', 'friendsofphp/php-cs-fixer'])) {
//            $this->error('Installation failed');
//            return 1;
//        }
        $config = \File::get(__DIR__ . '/../../../stubs/.php_cs.php');
        \File::put(base_path('.php_cs'), $config);
        $this->info('.php_cs was generated');
        if ($this->option('update-phpstorm-config')) {
            $this->updateInspectionProfiles();
        }
        return 0;
    }

    private function updateInspectionProfiles(): void
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
                'class' => 'PhpCSFixerValidationInspection',
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
        $this->info('PhpStorm configuration updated for CS Fixer');
    }
}
