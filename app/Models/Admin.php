<?php declare(strict_types = 1);

namespace App\Models;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Admin
 * @package App\Models
 */
class Admin extends Authenticatable
{
    use Notifiable;
    use ObfuscatesID;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];
}
