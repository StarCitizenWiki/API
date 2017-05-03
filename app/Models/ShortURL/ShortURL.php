<?php

namespace App\Models\ShortURL;

use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class ShortURL
 *
 * @package App\Models\ShortURL
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 * @property int $id
 * @property string $url
 * @property string $hash_name
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereHashName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUserId($value)
 * @property string $expires
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereExpires($value)
 */
class ShortURL extends Model
{
    protected $table = 'short_urls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'hash_name',
        'user_id',
    ];

    /**
     * Sets the User Relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Creates a shortened url
     *
     * @param array $data URL Data
     *
     * @return ShortURL
     */
    public static function createShortURL(array $data) : ShortURL
    {
        App::make('Log')->debug('Creating ShortURL');
        ShortURL::checkURLinWhitelist($data['url']);
        ShortURL::checkHashNameInDB($data['hash_name']);

        if (is_null($data['hash_name']) || empty($data['hash_name'])) {
            $data['hash_name'] = ShortURL::generateShortURLHash();
        }

        $url = new ShortURL();
        $url->url = $data['url'];
        $url->hash_name = $data['hash_name'];
        $url->user_id = $data['user_id'];
        $url->expires = $data['expires'];
        $url->save();

        App::make('Log')->info('URL Shortened', [
            'data' => $data,
        ]);

        return $url;
    }

    /**
     * Updates an existing url
     *
     * @param array $data URL Data
     *
     * @return bool
     *
     * @throws ModelNotFoundException
     */
    public static function updateShortURL(array $data) : bool
    {
        App::make('Log')->debug('Updating ShortURL', [
            'update_data' => $data,
        ]);
        ShortURL::checkURLinWhitelist($data['url']);
        $url = ShortURL::findOrFail($data['id']);

        if ($url->hash_name !== $data['hash_name']) {
            ShortURL::checkHashNameInDB($data['hash_name']);
        }

        $changes = [];
        $changes[] = ['updated_by' => Auth::id()];

        foreach ($data as $key => $value) {
            if ($url->$key != $value) {
                $changes[] = [$key.'_old' => $url->$key, $key.'_new' => $value];
                $url->$key = $value;
            }
        }

        App::make('Log')->info('ShortURL updated', [
            'changes' => $changes,
        ]);

        $url->url = $data['url'];
        $url->hash_name = $data['hash_name'];
        $url->user_id = $data['user_id'];
        $url->expires = $data['expires'];

        return $url->save();
    }

    /**
     * Resolves a url based on its hash
     *
     * @param String $hashName Name to resolve
     *
     * @return mixed
     * @throws ExpiredException
     * @throws UserBlacklistedException
     */
    public static function resolve(String $hashName)
    {
        $url = ShortURL::where('hash_name', '=', $hashName)->firstOrFail();

        App::make('Log')->debug('URL resolved', [
            'id' => $url->id,
            'hash_name' => $url->hash_name,
            'url' => $url->url,
            'expires' => $url->expires,
        ]);

        if (!is_null($url->expires) && Carbon::parse($url->expires)->lte(Carbon::now())) {
            throw new ExpiredException('Url has Expired');
        }

        if ($url->user()->first()->isBlacklisted()) {
            throw new UserBlacklistedException('User is blacklisted, can\'t resolve URL');
        }

        return $url;
    }

    /**
     * Sanitizes the given url and prepends '/' is missing
     * only if $url is a plain url without a path
     *
     * @param String $url URL to sanitize
     *
     * @return String
     */
    public static function sanitizeURL($url) : String
    {
        App::make('Log')->debug('Sanitizing URL', [
            'url' => $url,
        ]);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (!isset(parse_url($url)['path'])) {
            $url .= '/';
        }

        App::make('Log')->debug('Sanitized URL', [
            'url' => $url,
        ]);

        return $url;
    }

    /**
     * Checks if a given hash exists in the database
     *
     * @param String $hashName Hash to check
     *
     * @throws HashNameAlreadyAssignedException
     *
     * @return void
     */
    private static function checkHashNameInDB($hashName) : void
    {
        App::make('Log')->debug('Checking if hash is in DB', [
            'hash' => $hashName,
        ]);
        if (ShortURL::where('hash_name', '=', $hashName)->count() > 0) {
            App::make('Log')->debug('Hash is in DB');
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
        App::make('Log')->debug('Hash is not in DB');
    }

    /**
     * Checks if a given url host is whitelisted
     *
     * @param String $url URL to check
     *
     * @throws URLNotWhitelistedException
     *
     * @return void
     */
    private static function checkURLinWhitelist(String $url) : void
    {
        $url = parse_url($url, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        App::make('Log')->debug('Checking if URL is whitelisted', [
            'url' => $url,
        ]);

        if (ShortURLWhitelist::where('url', '=', $url)->count() !== 1) {
            App::make('Log')->debug('URL is not whitelisted');
            throw new URLNotWhitelistedException('Url '.$url.' is not whitelisted');
        }
        App::make('Log')->debug('URL is whitelisted');
    }

    /**
     * Creates a short url hash
     *
     * @return String
     */
    private static function generateShortURLHash() : String
    {
        App::make('Log')->debug('Generating Hash');
        do {
            $hashName = Str::random(SHORT_URL_LENGTH);
        } while (ShortURL::where('hash_name', '=', $hashName)->count() > 0);

        App::make('Log')->debug('Generated Hash', [
            'hash' => $hashName,
        ]);

        return $hashName;
    }
}
