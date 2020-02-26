<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use MortenScheel\PhpDependencyInstaller\Actions\Files\AddClassImport;
use MortenScheel\PhpDependencyInstaller\Actions\Files\AddTrait;
use MortenScheel\PhpDependencyInstaller\Actions\Files\AppendPhpArray;
use MortenScheel\PhpDependencyInstaller\Actions\Files\AppendPhpMethod;
use MortenScheel\PhpDependencyInstaller\Actions\Files\CaptureReplace;
use MortenScheel\PhpDependencyInstaller\Actions\Files\CopyFile;
use MortenScheel\PhpDependencyInstaller\Concerns\ReportsErrors;
use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Parser\ParserException;
use MortenScheel\PhpDependencyInstaller\Shell;
use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Support\Collection;

abstract class Action implements Arrayable
{
    use ReportsErrors;

    protected static $_action_class_map;
    /** @var Collection */
    protected static $_classmap;
    /** @var Filesystem */
    protected $filesystem;
    /** @var Shell */
    protected $shell;
    /** @var string|null */
    protected $dependency;

    public function __construct(array $item)
    {
        $this->dependency = array_get($item, 'depends');
        $this->filesystem = new Filesystem();
        $this->shell = new Shell();
    }

    abstract public function execute(): bool;

    /**
     * @param array $settings
     * @return Action
     * @throws ParserException
     */
    public static function make(array $settings)
    {
        $action_name = array_get($settings, 'type');
        if (!$action_name) {
            throw new ParserException('Expected item to have action');
        }
        $class = self::getActionClass($action_name);
        if (!$class) {
            throw new ParserException("Unknown Action: $action_name");
        }
        return new $class($settings);
    }

    protected static function getActionClass(string $name)
    {
        switch ($name) {
            case 'ComposerRequire':
            case 'composer-require':
                return ComposerRequire::class;
            case 'ArtisanCommand':
            case 'artisan-command':
            case 'artisan':
                return ArtisanCommand::class;
            case 'AddClassImport':
            case 'add-class-import':
                return AddClassImport::class;
            case 'AddTrait':
            case 'add-trait':
                return AddTrait::class;
            case 'AppendPhpArray':
            case 'append-php-array':
                return AppendPhpArray::class;
            case 'AppendPhpMethod':
            case 'append-php-method':
                return AppendPhpMethod::class;
            case 'CaptureReplace':
            case 'capture-replace':
                return CaptureReplace::class;
            case 'CopyFile':
            case 'copy-file':
                return CopyFile::class;
            case 'ShellCommand':
            case 'shell':
            case 'cli':
                return ShellCommand::class;
            default:
                return null;
        }
    }

    protected static function getClassBaseName(string $class)
    {
        return \basename(\str_replace('\\', '/', $class));
    }

    /**
     * @param string $class
     * @return string|null
     */
    protected static function findClassFile(string $class)
    {
        $key = \ltrim($class, "\\");
        return self::getClassMap()->get($key);
    }

    protected static function getClassMap()
    {
        if (!self::$_classmap) {
            $map = collect();
            $detect_global = (new Shell())->createComposerProcess([
                'config',
                '--global',
                'data-dir']);
            $detect_global->run();
            $global_root = rtrim($detect_global->getOutput());
            $global = $global_root . '/vendor/composer/autoload_classmap.php';
            if (file_exists($global)) {
                $map = collect(require $global);
            }
            $local = \getcwd() . '/vendor/composer/autoload_classmap.php';
            if (file_exists($local)) {
                $map = $map->merge(collect(require $local));
            }
            self::$_classmap = $map;
        }
        return self::$_classmap;
    }

    public function toArray()
    {
        $array = ['description' => $this->getDescription()];
        if (!empty($this->post_install_actions)) {
            $array['post_install'] = \array_map(function (Action $action) {
                return $action->toArray();
            }, $this->post_install_actions);
        }
        return $array;
    }

    abstract public function getDescription(): string;

    /**
     * @return string|null
     */
    public function getDependency(): ?string
    {
        return $this->dependency;
    }
}
