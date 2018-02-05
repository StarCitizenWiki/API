<?php declare(strict_types = 1);

namespace App\Models\ShortUrl;

use App\Traits\CanExpireTrait as CanExpire;
use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property string                $expired_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ShortUrl\ShortUrl whereExpires($value)
 */
class ShortUrl extends Model
{
    use SoftDeletes;
    use ObfuscatesID;
    use CanExpire;

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
     * Creates a short url hash
     *
     * @return string
     */
    public static function generateShortUrlHash(): String
    {
        do {
            $hashName = Str::random(config('shorturl.length'));
        } while (ShortUrl::where('hash', '=', $hashName)->count() > 0);

        app('Log')::info("Generated Hash: {$hashName}");

        return $hashName;
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
}
