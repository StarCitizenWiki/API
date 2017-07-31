<?php declare(strict_types = 1);

namespace App\Models\ShortURL;

use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class ShortURL
 *
 * @package App\Models\ShortURL
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 * @property int                   $id
 * @property string                $url
 * @property string                $hash_name
 * @property int                   $user_id
 * @property \Carbon\Carbon        $created_at
 * @property \Carbon\Carbon        $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereHashName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortURL\ShortURL whereUserId($value)
 * @property string                $expires
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
     * Creates a shortened url
     *
     * @param array $data URL Data
     *
     * @return ShortURL
     */
    public static function createShortURL(array $data): ShortURL
    {
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

        app('Log')::info('URL Shortened', ['data' => $data]);

        return $url;
    }

    /**
     * Resolves a url based on its hash
     *
     * @param string $hashName Name to resolve
     *
     * @return mixed
     * @throws ExpiredException
     * @throws UserBlacklistedException
     */
    public static function resolve(string $hashName)
    {
        $url = ShortURL::where('hash_name', '=', $hashName)->firstOrFail();

        app('Log')::info(
            'URL resolved',
            [
                'id'        => $url->id,
                'hash_name' => $url->hash_name,
                'url'       => $url->url,
                'expires'   => $url->expires,
            ]
        );

        if (!is_null($url->expires) && Carbon::parse($url->expires)->lte(Carbon::now())) {
            throw new ExpiredException('URL has Expired');
        }

        if ($url->user()->first()->isBlacklisted()) {
            throw new UserBlacklistedException('User is blacklisted, can\'t resolve URL');
        }

        return $url;
    }

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
     * Sanitizes the given url and prepends '/' is missing
     * only if $url is a plain url without a path
     *
     * @param string $url URL to sanitize
     *
     * @return String
     */
    public static function sanitizeURL($url): String
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        if (!isset(parse_url($url)['path'])) {
            $url .= '/';
        }

        return $url;
    }

    /**
     * @param string|null $date Date to check
     *
     * @throws ExpiredException
     */
    public static function checkIfDateIsPast($date): void
    {
        if (!is_null($date)) {
            $expires = Carbon::parse($date);
            if ($expires->lte(Carbon::now())) {
                throw new ExpiredException('Expires date can\'t be in the past');
            }
        }
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
    public static function updateShortURL(array $data): bool
    {
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

        app('Log')::info('ShortURL updated', ['changes' => $changes]);

        $url->url = $data['url'];
        $url->hash_name = $data['hash_name'];
        $url->user_id = $data['user_id'];
        $url->expires = $data['expires'];

        return $url->save();
    }

    /**
     * Checks if a given url host is whitelisted
     *
     * @param string $url URL to check
     *
     * @throws URLNotWhitelistedException
     *
     * @return void
     */
    private static function checkURLinWhitelist(string $url): void
    {
        $url = parse_url($url, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        if (ShortURLWhitelist::where('url', '=', $url)->count() !== 1) {
            throw new URLNotWhitelistedException('URL '.$url.' is not whitelisted');
        }
    }

    /**
     * Checks if a given hash exists in the database
     *
     * @param string $hashName Hash to check
     *
     * @throws HashNameAlreadyAssignedException
     *
     * @return void
     */
    private static function checkHashNameInDB($hashName): void
    {
        if (ShortURL::where('hash_name', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
    }

    /**
     * Creates a short url hash
     *
     * @return String
     */
    private static function generateShortURLHash(): String
    {
        do {
            $hashName = Str::random(SHORT_URL_LENGTH);
        } while (ShortURL::where('hash_name', '=', $hashName)->count() > 0);

        app('Log')::info("Generated Hash: {$hashName}");

        return $hashName;
    }
}
