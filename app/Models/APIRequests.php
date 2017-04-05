<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class APIRequests extends Model
{
    protected $table = 'api_requests';

    protected $fillable = [
        'user_id',
    ];
}
