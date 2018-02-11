<?php declare(strict_types = 1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class IsNotShortenedUrl
 */
class IsNotShortenedUrl implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !str_contains($value, parse_url(config('app.shorturl_url', PHP_URL_HOST)));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Url ist bereits gekürzt');
    }
}
