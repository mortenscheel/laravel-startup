<?php

namespace MortenScheel\PhpDependencyInstaller;

use MortenScheel\PhpDependencyInstaller\Actions\Action;
use MortenScheel\PhpDependencyInstaller\Actions\ArtisanCommand;
use MortenScheel\PhpDependencyInstaller\Actions\ComposerRequire;
use MortenScheel\PhpDependencyInstaller\Actions\ComposerRequireMultiple;
use MortenScheel\PhpDependencyInstaller\Parser\Recipe;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Tightenco\Collect\Support\Collection;

class ActionManager
{
    /**
     * @var Collection
     */
    private $recipes;
    /**
     * @var Shell
     */
    private $shell;

    public function __construct()
    {
        $this->recipes = collect();
        $this->shell = new Shell();
    }

    public function showActionsTable(OutputInterface $output, bool $optimize = true): void
    {
        $actions = $this->getActions($optimize);
        $table = new Table($output);
        $table->setStyle('compact');
        foreach ($actions as $index => $action) {
            $step = $index + 1;
            $description = $action->getDescription();
            $table->addRow([
                "<info>$step</info>",
                "<fg=white>$description</>"
            ]);
        }
        $table->render();
    }

    /**
     * @param bool $optimize
     * @return Collection|Action[]
     */
    protected function getActions(bool $optimize = true): Collection
    {
        $actions = $this->recipes->flatMap(function (Recipe $recipe) {
            return $recipe->getActions();
        });
        if (!$optimize) {
            return $actions;
        }
        return $this->optimizeActionOrder($actions);
    }

    /**
     * @param Collection $actions
     * @return Collection|Action[]
     */
    private function optimizeActionOrder(Collection $actions): Collection
    {
        $grouped = $actions->mapToGroups(function (Action $action) {
            $group = 'default';
            if ($action instanceof ComposerRequire) {
                $group = $action->dev ? 'require-dev' : 'require';
            } elseif ($action instanceof ArtisanCommand && $action->command === 'migrate') {
                $group = 'migrate';
            } elseif ($action->getDependency() === 'artisan migrate') {
                $group = 'post-migrate';
            }
            return [$group => $action];
        });
        $optimized = collect();
        /** @var Collection $require */
        $require = $grouped->get('require');
        /** @var Collection $require_dev */
        $require_dev = $grouped->get('require-dev');
        if ($require) {
            $skip_update = $require_dev && $require_dev->isNotEmpty();
            if ($require->count() === 1) {
                /** @var ComposerRequire $action */
                $action = $require->first();
                if ($skip_update) {
                    $action->skip_update = true;
                }
                $optimized->push($action);
            } else {
                $packages = $require->map(function (ComposerRequire $action) {
                    $package = $action->package;
                    if ($action->version) {
                        $package .= '=' . $action->version;
                    }
                    return $package;
                });
                $action = new ComposerRequireMultiple(['packages' => $packages, 'dev' => false]);
                if ($skip_update) {
                    $action->skip_update = true;
                }
                $optimized->push($action);
            }
        }
        if ($require_dev) {
            if ($require_dev->count() === 1) {
                $optimized->push($require_dev->first());
            } else {
                $packages = $require_dev->map(function (ComposerRequire $action) {
                    $package = $action->package;
                    if ($action->version) {
                        $package .= '=' . $action->version;
                    }
                    return $package;
                });
                $optimized->push(new ComposerRequireMultiple(['packages' => $packages, 'dev' => true]));
            }
        }
        /** @var Collection $default */
        if ($default = $grouped->get('default')) {
            $optimized = $optimized->merge($default->unique()->values());
        }
        /** @var Collection $migrate */
        if ($migrate = $grouped->get('migrate')) {
            $optimized->push($migrate->first());
        }
        /** @var Collection $post_migrate */
        if ($post_migrate = $grouped->get('post-migrate')) {
            $optimized = $optimized->merge($post_migrate);
        }
        return $optimized;
    }

    public function addRecipe(Recipe $recipe)
    {
        $this->recipes->push($recipe);
    }

    /**
     * @param OutputInterface $output
     * @param bool $optimize
     * @param bool $verbose
     * @return int
     */
    public function execute(OutputInterface $output, bool $optimize = true, bool $verbose = false)
    {
        if ($this->hasMigrateCommand() && !$this->canMigrate()) {
            $output->writeln(
                '<fg=red>Invalid database configuration detected.
The list of actions contains a database migration, which would have failed.
Installation aborted.</>'
            );
            return 1;
        }
        $actions = $this->getActions($optimize);
        foreach ($actions as $action) {
            $runner = new ActionRunner($action);
            if (!$runner->run($output, $verbose)) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @return mixed
     */
    public function hasMigrateCommand()
    {
        return $this->getActions()->contains(function (Action $action) {
            return $action instanceof ArtisanCommand && $action->command === 'migrate';
        });
    }

    private function canMigrate()
    {
        $status_process = $this->shell->createArtisanProcess(['migrate:status']);
        $success = $status_process->run() === 0;
        if ($success) {
            return true;
        }
        $status_output = $status_process->getOutput();
        if (\mb_stripos($status_output, 'migration table not found') !== false) {
            $install_process = $this->shell->createArtisanProcess(['migrate:install']);
            $install_process->run();
            return $install_process->getExitCode() === 0;
        }
        return false;
    }

    /**
     * @return Collection|Recipe[]
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    /**
     * @param Collection $recipes
     */
    public function setRecipes(Collection $recipes): void
    {
        $this->recipes = $recipes;
    }
}
