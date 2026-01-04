<?php

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 */
class Language extends Model
{
    use HasFactory;

    public const ENGLISH = 'en';

    public const GERMAN = 'de';

    public const CHINESE = 'zh';
}
