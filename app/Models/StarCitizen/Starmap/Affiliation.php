<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliation Model
 */
class Affiliation extends Model
{
    use HasFactory;

    protected $table = 'starmap_affiliations';

    protected $fillable = [
        'cig_id',
        'name',
        'code',
        'color',
        'membership_id',
    ];
}
