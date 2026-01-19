<?php

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function article(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
