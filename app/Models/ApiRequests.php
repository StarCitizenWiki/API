<?php declare(strict_types = 1);

namespace App\Models;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\APIRequests
 */
class ApiRequests extends Model
{
    use ObfuscatesID;

    protected $table = 'api_requests';

    protected $fillable = [
        'user_id',
        'request_uri',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }
}
