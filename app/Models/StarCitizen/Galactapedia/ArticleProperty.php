<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleProperty extends Model
{
    use HasFactory;

    protected $table = 'galactapedia_article_properties';

    protected $fillable = [
        'article_id',
        'name',
        'content',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
