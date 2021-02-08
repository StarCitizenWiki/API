<?php

namespace App\Models\Api\StarCitizen\Galactapedia;

use App\Models\System\Translation\AbstractTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends AbstractTranslation
{
    use HasFactory;

    protected $table = 'galactapedia_article_translations';

    protected $fillable = [
        'locale_code',
        'galactapedia_article_id',
        'translation',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
