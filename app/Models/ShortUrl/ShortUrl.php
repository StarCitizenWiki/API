<?php

namespace App\Models\ShortUrl;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
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
}
