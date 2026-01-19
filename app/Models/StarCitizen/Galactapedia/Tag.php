<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'galactapedia_tags';

    protected $fillable = [
        'cig_id',
        'name',
        'slug',
    ];

    public function article(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
