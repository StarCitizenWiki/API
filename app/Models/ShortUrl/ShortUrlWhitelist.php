<?php

namespace App\Models\ShortUrl;

use Illuminate\Database\Eloquent\Model;

class ShortUrlWhitelist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url'
    ];
}
