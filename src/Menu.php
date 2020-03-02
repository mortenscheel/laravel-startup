<?php

namespace MortenScheel\PhpDependencyInstaller;

use MortenScheel\PhpDependencyInstaller\Parser\PresetParser;
use MortenScheel\PhpDependencyInstaller\Repositories\PresetRepository;
use MortenScheel\PhpDependencyInstaller\Repositories\RecipeRepository;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Exception\InvalidTerminalException;
use PhpSchool\CliMenu\MenuItem\CheckboxItem;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuItem\MenuMenuItem;
use PhpSchool\CliMenu\MenuStyle;
use Symfony\Component\Finder\SplFileInfo;
use Tightenco\Collect\Support\Collection;

class Menu
{
    /** @var Collection */
    private $selected_recipes;
    private $selected_cookbook;
    /** @var Collection */
    private $available_recipes;
    /** @var bool */
    private $accepted = false;
    /** @var MenuStyle */
    private $whiteOnBlack;
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
        $this->shell = new Shell();
        $this->recipe_repository = new RecipeRepository();
        $this->preset_repository = new PresetRepository();
    }

    public function open()
    {
        $builder = new CliMenuBuilder();
        $this->setStyle($builder);
        $this->buildMainMenu($builder);
        $builder->setItemExtra('[i] info');
        $builder->enableAutoShortcuts();
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
            $builder->addStaticItem('Available presets');
            foreach ($presets as $name => $submenu) {
                $builder->addSubMenuFromBuilder($name, $submenu);
            }
            $builder->addLineBreak('-');
        }
        $builder->addStaticItem('Available recipes');
        foreach ($this->recipe_repository->all() as $recipe) {
            $builder->addCheckboxItem($recipe->getName(), function (CliMenu $menu) {
                $this->onRecipeToggled($menu);
            }, true);
        }
        $builder->addLineBreak();
        $builder->addSubMenuFromBuilder('Continue', $this->buildAcceptMenu());
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

    private function onRecipeToggled(CliMenu $menu)
    {
        $item = $menu->getSelectedItem();
        if ($item->getChecked()) {
            $this->selected_recipes->put($item->getText(), true);
        } else {
            $this->selected_recipes->forget($item->getText());
        }
        $old_accept_menu = $this->findItem($menu->getItems(), 'Continue');
        if ($old_accept_menu) {
            $menu->removeItem($old_accept_menu);
            $menu->addItem(new MenuMenuItem('Continue', $this->buildAcceptMenu()->build()));
            $menu->redraw();
        }
    }

    private function buildAcceptMenu()
    {
        $builder = new CliMenuBuilder;
        $this->setStyle($builder);
        if ($this->selected_recipes->isEmpty() && !$this->selected_cookbook) {
            $builder->setTitle('No recipes selected');
            $builder->addStaticItem('Please go back and select which recipes to install');
            $builder->addLineBreak();
            $builder->addItem('Go back', new GoBackAction());
            return $builder;
        } elseif ($this->selected_cookbook) {
            // Show cookbook recipes
            $a = 0;
        } else {
            $builder->setTitle('Confirm selection');
            foreach ($this->selected_recipes as $name => $selected_recipe) {
                $item = new CheckboxItem($name, function () {

                }, false, false);
                $item->setChecked();
                $builder->addMenuItem($item);
            }
            $builder->addLineBreak('-');
            $builder->addItem('Go back', new GoBackAction());
            $builder->addItem('Accept', function (CliMenu $menu) {
                $this->accepted = true;
                $menu->close();
            });

        }
        return $builder;
    }

    private function findItem(array $items, string $text): ?MenuItemInterface
    {
        return collect($items)->first(function (MenuItemInterface $item) use ($text) {
            return $item->getText() === $text;
        });
    }
}
