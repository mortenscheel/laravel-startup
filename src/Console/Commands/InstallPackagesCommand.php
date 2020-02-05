<?php

namespace MortenScheel\LaravelStartup\Console\Commands;

use Illuminate\Support\Collection;
use MortenScheel\LaravelStartup\Actions\FindInstallablePackages;
use MortenScheel\LaravelStartup\Packages\InstallablePackage;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;

class InstallPackagesCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-tools:install-packages {--yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install common packages';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \PhpSchool\CliMenu\Exception\InvalidTerminalException
     */
    public function handle()
    {
        $selected = collect();
        $menu = new CliMenuBuilder();
        $menu->setTitle('Select packages to install')
            ->setForegroundColour('black')
            ->setBackgroundColour('white')
            ->setMarginAuto();
        $callable = function (CliMenu $menu) use (&$selected) {
            /** @var CheckboxItem $item */
            $item = $menu->getSelectedItem();
            $name = $item->getText();
            $index = $selected->search($name);
            if ($index !== false) {
                $selected->forget($index);
            } else {
                $selected->push($name);
            }
        };
        /** @var Collection|InstallablePackage[] $packages */
        $packages = (new FindInstallablePackages)->run();
        $packages = $packages->mapWithKeys(function (InstallablePackage $package) {
            return [$package->getPackageName() => $package];
        });
        foreach ($packages as $package) {
            $item = new CheckboxItem(
                \sprintf('%s', $package->getPackageName()),
                $callable,
                false,
                $package->isInstalled()
            );
            if ($package->isInstalled()) {
                $item->setChecked();
            }
            $menu->addMenuItem($item);
        }
        $menu->build()->open();
        foreach ($selected as $name) {
            $package = $packages->get($name);
            $this->installPackage($package);
        }
    }

    private function installPackage(InstallablePackage $package): bool
    {
        $command = ['composer', 'require', $package->getPackageName()];
        if ($package->require_dev) {
            $command[] = '--dev';
        }
        $this->info("Installing {$package->getPackageName()} ({$package->getPackageDescription()})");
        if ($this->runProcess($command)) {
            $package->onInstalled();
            $this->info("{$package->getPackageName()} installed");
            return true;
        }
        return false;
    }
}
