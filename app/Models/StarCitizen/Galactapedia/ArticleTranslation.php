<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Translation
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
