<?php

namespace App\Models\ShortURL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class ShortURLWhitelist
 *
 * @package App\Models\ShortURL
 */
class ShortURLWhitelist extends Model
{
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
     * @return ShortURLWhitelist
     */
    public static function createWhitelistURL(array $data) : ShortURLWhitelist
    {
        $url = ShortURLWhitelist::create(
            [
                'url' => $data['url'],
                'internal' => $data['internal'],
            ]
        );

        Log::info(
            'Whitelist URL added',
            [
                'url' => $data['url'],
                'internal' => $data['internal'],
            ]
        );

        return $url;
    }
}
