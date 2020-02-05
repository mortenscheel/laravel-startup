<?php


namespace MortenScheel\LaravelStartup\Rules;


use Illuminate\Contracts\Validation\Rule;

class FileExistsRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        return \File::exists($value);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'File does not exist at :input';
    }
}
