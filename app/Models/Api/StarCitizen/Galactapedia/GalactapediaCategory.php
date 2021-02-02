<?php

namespace App\Models\Api\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalactapediaCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'cig_id',
        'name',
        'slug',
        'thumbnail'
    ];
}
