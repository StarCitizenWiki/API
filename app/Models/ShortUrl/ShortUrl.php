<?php

namespace App\Models\ShortURL;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Creates a shortened url
     * @param array $data
     * @return ShortURL
     */
    public static function createShortURL(array $data) : ShortURL
    {
        ShortURL::_checkURLinWhitelist($data['url']);
        ShortURL::_checkHashNameInDB($data['hash_name']);

        if (is_null($data['hash_name']) || empty($data['hash_name'])) {
            $data['hash_name'] = ShortURL::_generateShortURLHash();
        }

        Log::info('URL Shortened', [
            'url' => $data['url'],
            'hash_name' => $data['hash_name'],
            'user_id' => $data['user_id']
        ]);

        return ShortURL::create([
            'url' => $data['url'],
            'hash_name' => $data['hash_name'],
            'user_id' => $data['user_id']
        ]);
    }

    /**
     * updates an existing url
     * @param array $data
     * @return bool
     */
    public static function updateShortURL(array $data) : bool
    {
        ShortURL::_checkURLinWhitelist($data['url']);
        $url = ShortURL::find($data['id']);

        if ($url->hash_name !== $data['hash_name']) {
            ShortURL::_checkHashNameInDB($data['hash_name']);
        }

        Log::info('URL Updated', [
            'id' => $url->id,
            'owner' => $url->user()->first()->email,
            'updated_by' => Auth::user()->email,
            'old_url' => $url->url,
            'old_hash' => $url->hash_name,
            'new_url' => $data['url'],
            'new_hash' => $data['hash_name']
        ]);

        $url->url = $data['url'];
        $url->hash_name = $data['hash_name'];
        $url->user_id = $data['user_id'];

        return $url->save();
    }

    /**
     * resolves a url based on its hash
     * @param String $hashName
     * @throws ModelNotFoundException
     * @return mixed
     */
    public static function resolve(String $hashName)
    {
        $url = ShortURL::where('hash_name', '=', $hashName)->firstOrFail();
        return $url;
    }

    /**
     * Checks if a given hash exists in the database
     * @param String $hashName
     * @throws HashNameAlreadyAssignedException
     */
    private static function _checkHashNameInDB(String $hashName)
    {
        if (ShortURL::where('hash_name', '=', $hashName)->count() > 0) {
            throw new HashNameAlreadyAssignedException('Name already assigned');
        }
    }

    /**
     * checks if a given url host is whitelisted
     * @param String $url
     * @throws URLNotWhitelistedException
     */
    private static function _checkURLinWhitelist(String $url)
    {
        $url = parse_url($url, PHP_URL_HOST);
        $url = str_replace('www.', '', $url);

        if (ShortURLWhitelist::where('url', '=', $url)->count() !== 1) {
            throw new URLNotWhitelistedException('Url '.$url.' is not whitelisted');
        }
    }

    /**
     * Creates a short url hash
     * @return String
     */
    private static function _generateShortURLHash() : String
    {
        do {
            $hashName = Str::random(SHORT_URL_LENGTH);
        } while(ShortURL::where('hash_name', '=', $hashName)->count() > 0);
        return $hashName;
    }
}
