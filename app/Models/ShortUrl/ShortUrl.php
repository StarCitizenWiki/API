<?php

namespace App\Models\ShortURL;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public static function createShortURL(array $data) : ShortURL
    {
        ShortURL::_checkURLinWhitelist($data['url']);
        ShortURL::_checkHashNameInDB($data['hash_name']);

        if (is_null($data['hash_name']) || empty($data['hash_name'])) {
            $data['hash_name'] = ShortURL::_generateShortURLHash();
        }

        return ShortURL::create([
            'url' => $data['url'],
            'hash_name' => $data['hash_name'],
            'user_id' => $data['user_id']
        ]);
    }

    public static function updateShortURL(array $data) : bool
    {
        ShortURL::_checkURLinWhitelist($data['url']);
        $url = ShortURL::find($data['id']);

        if ($url->hash_name !== $data['hash_name']) {
            ShortURL::_checkHashNameInDB($data['hash_name']);
        }

        $url->url = $data['url'];
        $url->hash_name = $data['hash_name'];
        $url->user_id = $data['user_id'];

        return $url->save();
    }

    public static function resolve(String $hashName)
    {
        $url = ShortURL::where('hash_name', '=', $hashName)->firstOrFail();
        return $url;
    }

    private static function _checkHashNameInDB(String $hashName)
    {
        if (ShortURL::where('hash_name', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
    }

    private static function _checkURLinWhitelist(String $url)
    {
        $url = parse_url($url, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        if (ShortURLWhitelist::where('url', '=', $url)->count() !== 1) {
            throw new URLNotWhitelistedException('Url '.$url.' is not whitelisted');
        }
    }

    private static function _generateShortURLHash() : String
    {
        do {
            $hashName = Str::random(6);
        } while(ShortURL::where('hash_name', '=', $hashName)->count() > 0);
        return $hashName;
    }
}
