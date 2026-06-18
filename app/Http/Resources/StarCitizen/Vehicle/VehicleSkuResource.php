<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_sku',
    title: 'Vehicle SKU',
    properties: [
        new OA\Property(property: 'source', type: 'string', enum: ['upgrade_api', 'pledge_store']),
        new OA\Property(property: 'cig_id', type: 'integer'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'integer'),
        new OA\Property(property: 'discounted_price', type: 'integer', nullable: true),
        new OA\Property(property: 'is_warbond', type: 'boolean', nullable: true),
        new OA\Property(property: 'is_package', type: 'boolean', nullable: true),
        new OA\Property(property: 'available', type: 'boolean'),
        new OA\Property(property: 'imported_at', type: 'datetime'),
    ],
    type: 'object'
)]
class VehicleSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'source' => 'upgrade_api',
            'cig_id' => $this->cig_id,
            'title' => $this->title,
            'url' => null,
            'price' => $this->price,
            'discounted_price' => null,
            'is_warbond' => null,
            'is_package' => null,
            'available' => (bool) $this->available,
            'imported_at' => $this->created_at,
        ];
    }

    /**
     * Merge upgrade-API and pledge-store SKU arrays, deduplicating by cig_id.
     *
     * Pledge-store rows win on collision (richer data + purchase URL); an
     * upgrade-API SKU is only kept when no pledge-store SKU shares its cig_id
     * (CCU-only hulls that aren't sold standalone).
     *
     * @param  array<int, array<string, mixed>>  $upgradeSkus
     * @param  array<int, array<string, mixed>>  $pledgeSkus
     * @return array<int, array<string, mixed>>
     */
    public static function combine(array $upgradeSkus, array $pledgeSkus): array
    {
        $pledgeCigIds = collect($pledgeSkus)->pluck('cig_id')->filter()->unique();

        $orphanUpgradeSkus = collect($upgradeSkus)
            ->reject(fn (array $sku) => $pledgeCigIds->contains($sku['cig_id']));

        return collect($pledgeSkus)
            ->concat($orphanUpgradeSkus)
            ->values()
            ->toArray();
    }
}
