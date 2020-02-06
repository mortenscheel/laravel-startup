<?php


namespace MortenScheel\LaravelBlitz\Actions;


class CaptureReplace extends Action
{
    private $file;
    private $capture;
    private $replacement;

    /**
     * CaptureReplace constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct();
        $this->file = $item['file'];
        $this->capture = $item['capture'];
        $this->replacement = $item['replacement'];
    }


    public function getDescription(): string
    {
        return \sprintf('Replace %s with %s in %s', $this->capture, $this->replacement, $this->file);
    }

    public function execute(): bool
    {
        $file = $this->filesystem->get($this->file);
        if (\preg_match($this->capture, $file, $match, \PREG_OFFSET_CAPTURE)) {
            [$captured, $offset] = $match[1];
            $file = \sprintf(
                '%s%s%s',
                \mb_substr($file, 0, $offset),
                $this->replacement,
                \mb_substr($file, $offset + \mb_strlen($captured))
            );
            $this->filesystem->put($this->file, $file);
            return true;
        }
        $this->error = "{$this->capture} didn't capture anything";
        return false;
    }
}
