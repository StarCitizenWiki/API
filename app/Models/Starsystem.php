<?php declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Starsystem
 *
 * @package App\Models
 * @mixin \Eloquent
 * @property int            $id
 * @property string         $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereUpdatedAt($value)
 * @property int            $exclude
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Starsystem whereExclude($value)
 */
class Starsystem extends Model
{
    protected $fillable = [
        'code',
    ];

    /**
     * @param string $code
     *
     * @return String
     */
    public static function makeFilenameFromCode(string $code): String
    {
        return $code.'_System.json';
    }

    /**
     * @return bool
     */
    public function isExcluded(): bool
    {
        return (bool) $this->exclude;
    }
}
