<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleRelate extends Model
{
    use HasFactory;

    protected $table = 'galactapedia_article_relates';
}
