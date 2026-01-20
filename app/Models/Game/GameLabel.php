<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GameLabel extends Model
{
    use HasFactory, HasTranslations;

    public $incrementing = false;

    protected $keyType = 'string';

    public array $translatable = ['translation'];

    protected $table = 'game_labels';

    protected $fillable = [
        'id',
        'key',
        'translation',
    ];
}
