<?php declare(strict_types = 1);

namespace App\Models\ShortUrl;

use App\Traits\ObfuscatesIDTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShortUrlWhitelist
 */
class ShortUrlWhitelist extends Model
{
    use ObfuscatesIDTrait;

    protected $table = 'short_url_whitelists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'internal',
    ];

    /**
     * Creates a shortened url
     *
     * @param array $data URL Data
     *
     * @return \App\Models\ShortUrl\ShortUrlWhitelist
     */
    public static function createWhitelistUrl(array $data): ShortUrlWhitelist
    {
        $url = ShortUrlWhitelist::create(
            [
                'url'      => $data['url'],
                'internal' => $data['internal'],
            ]
        );

        app('Log')::info(
            'Whitelist URL added',
            [
                'url'      => $data['url'],
                'internal' => $data['internal'],
            ]
        );

        return $url;
    }
}
