<?php

namespace App\Models\ShortURL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * Class ShortURLWhitelist
 *
 * @package App\Models\ShortURL
 * @mixin \Eloquent
 * @property int $id
 * @property string $url
 * @property bool $internal
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURLWhitelist whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURLWhitelist whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURLWhitelist whereInternal($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURLWhitelist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURLWhitelist whereUrl($value)
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
        $url = ShortURLWhitelist::create([
            'url' => $data['url'],
            'internal' => $data['internal'],
        ]);

        App::make('Log')::info('Whitelist URL added', [
            'url' => $data['url'],
            'internal' => $data['internal'],
        ]);

        return $url;
    }
}
