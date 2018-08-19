<?php declare(strict_types = 1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 */
class Language extends Model
{
    protected $primaryKey = 'locale_code';
    public $incrementing = false;
}
