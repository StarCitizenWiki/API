<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantGradeItem extends Model
{
    public $timestamps = false;

    protected $table = 'game_item_variant_grade_items';

    protected $fillable = [
        'item_data_id',
        'grade_item_data_id',
    ];

    public function gradeItem(): BelongsTo
    {
        return $this->belongsTo(ItemData::class, 'grade_item_data_id');
    }
}
