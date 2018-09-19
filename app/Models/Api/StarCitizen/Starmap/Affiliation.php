<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 04.08.2018 19:58
 */

namespace App\Models\Api\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Affiliation Model
 */
class Affiliation extends Model
{
    protected $fillable = [
        'id',
        'name',
        'code',
        'color',
    ];

    public $timestamps = false;
}
