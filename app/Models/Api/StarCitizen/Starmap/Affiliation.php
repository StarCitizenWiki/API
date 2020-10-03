<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Affiliation Model
 */
class Affiliation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cig_id',
        'name',
        'code',
        'color',
        'membership_id',
    ];
}
