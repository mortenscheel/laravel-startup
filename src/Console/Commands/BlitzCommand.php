<?php


namespace MortenScheel\LaravelBlitz\Console\Commands;

use Illuminate\Support\Arr;
use MortenScheel\LaravelBlitz\Actions\FileManipulation\AppendPhpArray;
use Symfony\Component\Yaml\Yaml;

class BlitzCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blitz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap project according to blitz.yml';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = Yaml::parseFile(base_path('blitz.yml'));
        if ($git = Arr::get($config, 'git')) {
            $this->initializeGit($git);
        }
        if ($packages = Arr::get($config, 'packages')) {
            $this->installPackages($packages);
        }
    }

    private function initializeGit(array $git): void
    {
        // @todo check if already initialized
        if (Arr::get($git, 'init')) {
            $this->runProcess(['git', 'init']);
            $this->info('Git repository initialized');
        }
        // @todo add log file to .gitignore
        // @todo skip if not dirty
        if ($message = Arr::get($git, 'commit')) {
            $this->runProcess(['git', 'add', '.']) &&
            $this->runProcess(['git', 'commit', '-m', $message]);
            $this->info('Git commit created with message: ' . $message);
        }
        // @todo check if already added
        if ($submodules = Arr::get($git, 'submodules')) {
            foreach ($submodules as $submodule) {
                $this->runProcess(['git', 'submodule', 'add', $submodule]);
                $this->info('Git submodule added: ' . $submodule);
            }
        }
    }

    private function isInstalled(string $name, string $version = null)
    {
        $command = [
            'composer',
            'show',
            '--quiet',
            $name,
        ];
        if ($version) {
            $command[] = $version;
        }
        return $this->runProcess($command);
    }

    private function installPackages(array $packages): void
    {
        foreach ($packages as $name => $settings) {
            $this->info('Installing ' . $name);
            $version = Arr::get($settings, 'version');
            if ($this->isInstalled($name, $version)) {
                $this->warn("$name is already installed. Skipping.");
                continue;
            }
            $install_command = [
                'composer',
                'require',
                $name,
                '--no-interaction',
                '--no-suggest'
            ];
            if (Arr::get($settings, 'dev')) {
                $install_command[] = '--dev';
            }
            if ($this->runProcess($install_command)) {
                if ($post_install = Arr::get($settings, 'post-install')) {
                    foreach ($post_install as $action => $action_params) {
                        switch ($action) {
                            case 'publish':
                                $command = 'vendor:publish';
                                if ($tag = Arr::get($action_params, 'tag')) {
                                    $this->runArtisanCommand($command, ['--tag' => $tag]);
                                } elseif ($provider = Arr::get($action_params, 'provider')) {
                                    $this->runArtisanCommand($command, ['--provider' => $provider]);
                                }
                                break;
                            case 'append-array':
                                $file = Arr::get($action_params, 'file');
                                $array = Arr::get($action_params, 'array');
                                $value = Arr::get($action_params, 'value');
                                if ($file && $array && $value !== null) {
                                    (new AppendPhpArray(compact('file', 'array', 'value')))->run();
                                }
                                break;
                            case 'command':
                                if ($command = Arr::get($action_params, 'name')) {
                                    $args = Arr::get($action_params, 'arguments', []);
                                    $this->runArtisanCommand($command, $args);
                                }
                                break;
                        }
                    }
                }
            }
        }
    }
}
