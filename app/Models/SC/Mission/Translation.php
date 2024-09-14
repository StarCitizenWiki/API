<?php

namespace App\Models\SC\Mission;

use App\Models\System\Translation\AbstractTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Translation extends AbstractTranslation
{
    use HasFactory;

    protected $table = 'sc_mission_translations';

    protected $fillable = [
        'locale_code',
        'item_uuid',
        'translation',
    ];
}
