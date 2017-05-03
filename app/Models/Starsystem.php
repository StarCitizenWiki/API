<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Starsystem
 *
 * @package App\Models
 * @mixin \Eloquent
 * @property int $id
 * @property string $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereUpdatedAt($value)
 */
class Starsystem extends Model
{
    protected $fillable = [
        'code',
    ];

    /**
     * @return bool
     */
    public function isExcluded() : bool
    {
        return (bool) $this->exclude;
    }

    /**
     * @param String $code
     *
     * @return String
     */
    public static function makeFilenameFromCode(String $code) : String
    {
        return $code.'_System.json';
    }
}
