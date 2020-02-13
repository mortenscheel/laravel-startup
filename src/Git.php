<?php


namespace MortenScheel\LaravelBlitz;


use MortenScheel\LaravelBlitz\Concerns\ProcessRunner;

class Git
{
    use ProcessRunner;

    public function isExecutable()
    {
        return $this->shell(['git', '--version']) === true;
    }

    public function isRepo()
    {
        $files = new Filesystem();
        return $files->exists($files->getAbsolutePath('.git'));
    }

    public function isDirty()
    {
        $this->shell(['git', 'status', '--short']);
        return $this->process_output !== '';
    }

    public function init()
    {
        return $this->shell(['git', 'init']);
    }

    public function add(string $path = '.')
    {
        return $this->shell(['git', 'add', $path]);
    }

    public function commit(string $message)
    {
        return $this->shell(['git', 'commit', '-m', $message]);
    }
}
