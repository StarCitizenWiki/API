<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Affiliation Model
 */
class Affiliation extends Model
{
    protected $table = 'starmap_affiliations';

    protected $fillable = [
        'cig_id',
        'name',
        'code',
        'color',
        'membership_id',
    ];
}
