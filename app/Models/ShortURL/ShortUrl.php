<?php declare(strict_types = 1);

namespace App\Models\ShortUrl;

use App\Traits\CanExpireTrait as CanExpire;
use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class ShortUrl
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
    public static function generateShortUrlHash(): string
    {
        do {
            $hashName = Str::random(config('shorturl.length'));
        } while (ShortUrl::where('hash', '=', $hashName)->count() > 0);

        app('Log')::info("Generated Hash: {$hashName}");

        return $hashName;
    }

    /**
     * @return string
     */
    public function getFullShortUrl(): string
    {
        if (!is_null($this->hash)) {
            return config('app.shorturl_url').'/'.$this->hash;
        }

        return config('app.shorturl_url').'/';
    }
}
