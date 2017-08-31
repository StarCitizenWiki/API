<?php declare(strict_types = 1);

namespace App\Rules;

use App\Models\ShortUrl\ShortUrlWhitelist;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ShortUrl
 * @package App\Rules
 */
class ShortUrlWhitelisted implements Rule
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
        $url = parse_url($value, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        if (ShortUrlWhitelist::where('url', $url)->count() !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Die Domain der eingegebenen URL ist nicht freigeschaltet');
    }
}
