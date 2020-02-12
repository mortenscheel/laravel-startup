<?php

namespace MortenScheel\LaravelBlitz\Actions;

use Tightenco\Collect\Support\Collection;

class ActionCollection extends Collection
{
    public function listActions(): string
    {
        $width = \mb_strlen($this->count());
        return $this->map(function (ActionInterface $action, $index) use ($width) {
            return \sprintf("<info>%{$width}d</info> %s", $index + 1, $action->getDescription());
        })->join(\PHP_EOL);
    }

    /**
     * @param bool $dev
     * @return ActionCollection
     */
    public function getInstallablePackages(bool $dev = false)
    {
        return $this->filter(function (ActionInterface $action) use ($dev) {
            if (!$action instanceof ComposerInstall || $action->isInstalled()) {
                return false;
            }
            return $action->dev === $dev;
        });
    }

    /**
     * @return ActionCollection
     */
    public function getPostInstallActions()
    {
        return $this->filter(function (ActionInterface $action) {
            return !($action instanceof ComposerInstall);
        });
    }

    /**
     * @return mixed
     */
    public function getMigrateCommand()
    {
        return $this->first(function (ActionInterface $action) {
            return $action instanceof ArtisanCommand && $action->command === 'migrate';
        });
    }
}
