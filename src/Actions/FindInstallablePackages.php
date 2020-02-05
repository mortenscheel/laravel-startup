<?php

namespace MortenScheel\LaravelStartup\Actions;

use MortenScheel\LaravelStartup\Packages\InstallablePackage;

class FindInstallablePackages extends BaseAction
{
    private $ignored_namespaces = [
        'Illuminate',
        'Symfony',
        'Faker',
        'PhpCsFixer',
        'Composer',
        'PhpParser',
        'PHPUnit',
        'League',
        'Psy',
        'Facade',
        'SebastianBergmann',
        'Monolog',
        'Mockery',
        'Prophecy',
        'phpDocumentor',
        'GuzzleHttp',
        'Hamcrest',
        'PharIo',
        'Egulias',
        'Ramsey',
        'JsonSchema',
        'Carbon',
        'Doctrine',
        'Dotenv',
        'DeepCopy',
        'Psr',
        'JeroenG',
        'Whoops',
        'NunoMaduro',
        'Lorisleiva',
        'Highlight',
        'Opis'];

    public function handle()
    {
        $class_map = require base_path('vendor/composer/autoload_classmap.php');
        return collect($class_map)
            ->keys()
            ->filter(function (string $fqn) {
                $top_namespace = \explode('\\', $fqn)[0];
                if (\in_array($top_namespace, $this->ignored_namespaces, true)) {
                    return false;
                }
                if (!\class_exists($fqn)) {
                    return false;
                }
                try {
                    $reflection = new \ReflectionClass($fqn);
                    return $reflection->isSubclassOf(InstallablePackage::class);
                } catch (\Throwable $e) {
                    return false;
                }
            })->values()
            ->map(static function (string $class) {
                return new $class;
            });
    }
}
