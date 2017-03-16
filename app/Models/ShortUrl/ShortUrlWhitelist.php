<?php

namespace App\Models\ShortURL;

use Illuminate\Database\Eloquent\Model;

class ShortURLWhitelist extends Model
{
    protected $table = 'short_url_whitelists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url'
    ];
}
