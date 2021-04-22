<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\CharArmor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CharArmorAttachment extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_char_armor_attachments';

    protected $fillable = [
        'name',
        'min_size',
        'max_size',
    ];

    protected $casts = [
        'min_size' => 'int',
        'max_size' => 'int',
    ];

    public function armor(): BelongsToMany
    {
        return $this->belongsToMany(
            CharArmor::class,
            'star_citizen_unpacked_char_armor_attachment'
        );
    }
}
