<?php


namespace MortenScheel\LaravelStartup\Rules;


use Illuminate\Contracts\Validation\Rule;

class FileIsWriteableRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        return \File::isWritable($value);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'File at :input is not writable';
    }
}
