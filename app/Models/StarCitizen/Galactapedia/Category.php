<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'galactapedia_categories';

    protected $fillable = [
        'cig_id',
        'name',
        'slug',
        'thumbnail',
    ];
}
