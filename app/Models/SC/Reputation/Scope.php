<?php

namespace App\Models\SC\Reputation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Scope extends Model
{
    use HasFactory;

    protected $table = 'sc_reputation_scopes';

    protected $fillable = [
        'uuid',
        'scope_name',
        'display_name',
        'description',
        'class_name',
        'initial_reputation',
        'reputation_ceiling',
    ];

    public function standings(): BelongsToMany
    {
        return $this->belongsToMany(
            Standing::class,
            'sc_reputation_scope_standing',
        );
    }
}
