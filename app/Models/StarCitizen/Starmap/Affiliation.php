<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    /**
     * Upsert affiliations from an SC starmap payload and return their ids for syncing.
     *
     * @return array<int, int>
     */
    public static function syncFromPayload(array $affiliations): array
    {
        return collect($affiliations)
            ->filter(
                function ($affiliation) {
                    return isset($affiliation['id']);
                }
            )
            ->map(
                function ($affiliationData) {
                    return static::query()->updateOrCreate(['cig_id' => $affiliationData['id']], [
                        'name' => Arr::get($affiliationData, 'name'),
                        'code' => Arr::get($affiliationData, 'code'),
                        'color' => Arr::get($affiliationData, 'color'),
                        'membership_id' => Arr::get($affiliationData, 'membership.id', null),
                    ]);
                }
            )
            ->map(
                function (self $affiliation) {
                    return $affiliation->id;
                }
            )
            ->toArray();
    }
}
