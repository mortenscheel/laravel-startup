<?php

namespace MortenScheel\PhpDependencyInstaller\Commands;

use MortenScheel\PhpDependencyInstaller\Concerns\RunsShellCommands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DebugCommand extends Command
{
    use RunsShellCommands;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('debug')
            ->setDescription('Command for ad.hoc debugging during development');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $test = [
            ['laravel-passport' => [
                'description' => 'Laravel Passport provides OAuth2 server support to Laravel.',
                'url' => 'https://laravel.com/docs/master/passport',
                'alias' => 'passport',
                'action' => 'ComposerInstall',
                'package' => 'laravel/laravel-passport',
                'then' => [
                    [
                        'action' => 'ArtisanCommand',
                        'command' => 'migrate'
                    ],
                    [
                        'action' => 'ArtisanCommand',
                        'command' => 'passport:install'
                    ]
                ]
            ]],
            ['laravel-ide-helper' => [
                'description' => 'Laravel IDE helper',
                'url' => 'https://github.com/barryvdh/laravel-ide-helper',
                'alias' => 'ide-helper',
                'action' => 'composerinstall',
            ]],
        ];
        $yaml = new Yaml();
        $result = $yaml->dump($test, 5);
//        $result = \json_encode($test, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
        $output->writeln($result);
    }
}
