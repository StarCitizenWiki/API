<?php declare(strict_types = 1);

namespace App\Models\ShortUrl;

use App\Exceptions\ExpiredException;
use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\UrlNotWhitelistedException;
use App\Exceptions\UserBlacklistedException;
use App\Traits\ObfuscatesIDTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class ShortUrl
 *
 * @package App\Models\ShortUrl
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 * @property int                   $id
 * @property string                $url
 * @property string                $hash
 * @property int                   $user_id
 * @property \Carbon\Carbon        $created_at
 * @property \Carbon\Carbon        $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereHashName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereUserId($value)
 * @property string                $expires
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereExpires($value)
 */
class ShortUrl extends Model
{
    use SoftDeletes;
    use ObfuscatesIDTrait;

    protected $table = 'short_urls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'hash',
        'user_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expired_at',
    ];

    /**
     * Creates a shortened url
     *
     * @param array $data URL Data
     *
     * @return \App\Models\ShortUrl\ShortUrl
     */
    public static function createShortUrl(array $data): ShortUrl
    {
        ShortUrl::checkUrlinWhitelist($data['url']);
        ShortUrl::checkHashNameInDB($data['hash']);

        if (is_null($data['hash']) || empty($data['hash'])) {
            $data['hash'] = ShortUrl::generateShortUrlHash();
        }

        $url = new ShortUrl();
        $url->url = $data['url'];
        $url->hash = $data['hash'];
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
     *
     * @throws \App\Exceptions\ExpiredException
     * @throws \App\Exceptions\UserBlacklistedException
     */
    public static function resolve(string $hashName)
    {
        $url = ShortUrl::where('hash', '=', $hashName)->firstOrFail();

        app('Log')::info(
            'URL resolved',
            [
                'id'        => $url->id,
                'hash' => $url->hash,
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
     * Sanitizes the given url and prepends '/' is missing
     * only if $url is a plain url without a path
     *
     * @param string $url URL to sanitize
     *
     * @return string
     */
    public static function sanitizeUrl($url): String
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
     * @throws \App\Exceptions\ExpiredException
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
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function updateShortUrl(array $data): bool
    {
        ShortUrl::checkUrlinWhitelist($data['url']);
        $url = ShortUrl::findOrFail($data['id']);

        if ($url->hash !== $data['hash']) {
            ShortUrl::checkHashNameInDB($data['hash']);
        }

        $changes = [];
        $changes[] = ['updated_by' => Auth::id()];

        foreach ($data as $key => $value) {
            if ($url->$key != $value) {
                $changes[] = [$key.'_old' => $url->$key, $key.'_new' => $value];
                $url->$key = $value;
            }
        }

        app('Log')::info('ShortUrl updated', ['changes' => $changes]);

        $url->url = $data['url'];
        $url->hash = $data['hash'];
        $url->user_id = $data['user_id'];
        $url->expires = $data['expires'];

        return $url->save();
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
     * Checks if a given url host is whitelisted
     *
     * @param string $url URL to check
     *
     * @throws \App\Exceptions\UrlNotWhitelistedException
     *
     * @return void
     */
    private static function checkUrlinWhitelist(string $url): void
    {
        $url = parse_url($url, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        if (ShortUrlWhitelist::where('url', '=', $url)->count() !== 1) {
            throw new UrlNotWhitelistedException('URL '.$url.' is not whitelisted');
        }
    }

    /**
     * Checks if a given hash exists in the database
     *
     * @param string $hashName Hash to check
     *
     * @throws \App\Exceptions\HashNameAlreadyAssignedException
     *
     * @return void
     */
    private static function checkHashNameInDB($hashName): void
    {
        if (ShortUrl::where('hash', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
    }

    /**
     * Creates a short url hash
     *
     * @return string
     */
    private static function generateShortUrlHash(): String
    {
        do {
            $hashName = Str::random(SHORT_URL_LENGTH);
        } while (ShortUrl::where('hash', '=', $hashName)->count() > 0);

        app('Log')::info("Generated Hash: {$hashName}");

        return $hashName;
    }
}
