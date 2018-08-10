<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Stat;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Stats
 */
class Stat extends Model
{
    protected $fillable = [
        'funds',
        'fans',
        'fleet',
    ];
}
