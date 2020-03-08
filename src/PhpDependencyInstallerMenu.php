<?php

namespace MortenScheel\PhpDependencyInstaller;

use MortenScheel\PhpDependencyInstaller\Parser\Preset;
use MortenScheel\PhpDependencyInstaller\Repositories\PresetRepository;
use MortenScheel\PhpDependencyInstaller\Repositories\RecipeRepository;
use PhpSchool\CliMenu\Action\ExitAction;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Exception\InvalidTerminalException;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuItem\MenuMenuItem;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use PhpSchool\CliMenu\MenuItem\StaticItem;
use PhpSchool\CliMenu\MenuStyle;
use Symfony\Component\Finder\SplFileInfo;
use Tightenco\Collect\Support\Collection;

class PhpDependencyInstallerMenu
{
    /** @var Collection */
    private $selected_recipes;
    private $selected_preset;
    /** @var Collection */
    private $available_recipes;
    /** @var bool */
    private $accepted = false;
    /** @var MenuStyle */
    private $whiteOnBlack;
    /** @var MenuStyle */
    private $successStyle;
    /** @var MenuStyle */
    private $errorStyle;
    /** @var Filesystem */
    private $filesystem;

    /**
     * @var Shell
     */
    private $shell;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->selected_recipes = collect();
        $this->whiteOnBlack = (new MenuStyle())->setBg('black')->setFg('white');
        $this->successStyle = (new MenuStyle())->setBg('green')->setFg('white');
        $this->errorStyle = (new MenuStyle())->setBg('red')->setFg('white');
        $this->shell = new Shell();
        $this->recipe_repository = new RecipeRepository();
        $this->preset_repository = new PresetRepository();
    }

    public function open()
    {
        $builder = new CliMenuBuilder();
        $this->setStyle($builder);
//        $builder->enableAutoShortcuts();
        $this->buildMainMenu($builder);
        $menu = $builder->build();
        $menu->addCustomControlMapping('i', function (CliMenu $menu) {
            $selected = $menu->getSelectedItem()->getText();
            $description = $this->available_recipes->get($selected)['description'];
            $flash = $menu->flash($description);
            $flash->getStyle()->setBg('blue')->setFg('black');
            $flash->display();
        });
        $menu->addCustomControlMapping('o', function (CliMenu $menu) {
            $selected = $menu->getSelectedItem()->getText();
            $url = $this->available_recipes->get($selected)['url'];
            $this->shell->execute(['open', $url]);
        });
        try {
            $menu->open();
            return $this->accepted ? $this->selected_recipes->keys() : null;
        } catch (InvalidTerminalException $e) {
            return null;
        }
    }

    private function setStyle(CliMenuBuilder $builder)
    {
        $style = $builder->getStyle();
        $width = $style->getWidth() - 2 * $style->getPaddingLeftRight();
        $text = '(i) info (o) open in browser';
        $title = \sprintf("%{$width}s", $text);
        $builder->setMarginAuto()
            ->setBackgroundColour('white')
            ->setForegroundColour('black')
            ->setTitle($title)
            ->setExitButtonText('Cancel');
    }

    private function buildMainMenu(CliMenuBuilder $builder)
    {
        $presets = $this->generatePresetSubmenus();
        if (!empty($presets)) {
//            $builder->addStaticItem('Available presets');
//            foreach ($presets as $name => $submenu) {
//                $builder->addSubMenuFromBuilder($name, $submenu);
//            }
        } else {
            $builder->addMenuItem(new StaticItem('No presets found'));
        }
        $builder->addLineBreak();
        $builder->addStaticItem('Select recipes');
        foreach ($this->recipe_repository->all() as $recipe) {
            $checkbox = new CheckboxItem($recipe->getName(), function (CliMenu $menu) use ($recipe) {
                $item = $menu->getSelectedItem();
                if ($item->getChecked()) {
                    $this->selected_recipes->put($item->getText(), $recipe);
                } else {
                    $this->selected_recipes->forget($item->getText());
                }
                /** @var MenuMenuItem $continue_item */
                $continue_item = $this->findItem($menu->getItems(), 'Continue');
                $accept_menu = $continue_item->getSubMenu();
                $this->updateAcceptMenu($accept_menu);
                $menu->redraw();
            });
            if ($this->selected_recipes->has($recipe->getName())) {
                $checkbox->setChecked();
            }
            $builder->addMenuItem($checkbox);
        }
        $builder->addLineBreak();
        $builder->addSubMenu('Continue', function (CliMenuBuilder $builder) {
            $builder->setTitle('No recipes selected')
                ->addStaticItem('Please go back and select at least one recipe');
        });
        $builder->setExitButtonText('Exit');
    }

    private function generatePresetSubmenus()
    {
        $presets = [];
        /** @var SplFileInfo $file */
        foreach ($this->preset_repository->all() as $preset) {
            $submenu = (new CliMenuBuilder())->setTitle('Preset: ' . $preset->getName())
                ->addStaticItem('Included recipes')
                ->addLineBreak('-');
            foreach ($preset->getRecipes() as $recipe) {
                $name = $recipe->getName();
                if ($description = $recipe->getDescription()) {
                    $name .= " ($description)";
                }
                $checkbox = new CheckboxItem($name, function () {
                }, false, true);
                $checkbox->setChecked();
                $submenu->addMenuItem($checkbox);
            }
            $submenu->addItem('Go back', new GoBackAction());
            $presets[$preset->getName()] = $submenu;
        }
        return $presets;
    }

    private function findItem(array $items, string $text): ?MenuItemInterface
    {
        return collect($items)->first(function (MenuItemInterface $item) use ($text) {
            return $item->getText() === $text;
        });
    }

    private function updateAcceptMenu(CliMenu $accept_menu)
    {
        foreach ($accept_menu->getItems() as $item) {
            $accept_menu->removeItem($item);
        }
        if ($this->selected_recipes->isEmpty() && !$this->selected_preset) {
            $accept_menu->setTitle('No recipes selected');
            $accept_menu->addItem(new StaticItem('Please go back and select which recipes to install'));
            $accept_menu->addItem(new LineBreakItem());
            $accept_menu->addItem(new SelectableItem('Go back', new GoBackAction()));
        } elseif ($this->selected_preset) {
            // Show preset recipes
            $a = 0;
        } else {
            $accept_menu->setTitle('Confirm selection');
            foreach ($this->selected_recipes as $name => $selected_recipe) {
                $accept_menu->addItem(new StaticItem("- $name"));
            }
            $accept_menu->addItem(new LineBreakItem());
            $accept_menu->addItem(new SelectableItem('Install', function (CliMenu $menu) {
                $this->accepted = true;
                $menu->close();
            }));
            $accept_menu->addItem(new SelectableItem('Save preset', function (CliMenu $menu) {
                $presets = $this->preset_repository;
                $name = $menu->askText($this->whiteOnBlack)
                    ->setPromptText('Name the preset')
                    ->setPlaceholderText('my-preset')
                    ->setValidator(function ($name) use ($presets) {
                        if ($presets->get($name)) {
                            $this->setValidationFailedText($name . ' already exists');
                            return false;
                        }
                        if ($name === '') {
                            $this->setValidationFailedText('Name is required');
                            return false;
                        }
                        return true;
                    })->ask()
                    ->fetch();
                $recipes = $this->selected_recipes->keys()
                    ->map(function ($recipe_name) {
                        return $this->recipe_repository->get($recipe_name);
                    });
                $preset = new Preset($name, $recipes);
                if ($preset->save()) {
                    $menu->flash($name . ' saved', $this->successStyle)->display();
                } else {
                    $menu->flash('Unable to save ' . $name, $this->errorStyle)->display();
                }
            }));
            $accept_menu->addItem(new LineBreakItem());
            $accept_menu->addItem(new SelectableItem('Go back', new GoBackAction()));
            $accept_menu->addItem(new SelectableItem('Exit', new ExitAction()));
        }
    }
}
