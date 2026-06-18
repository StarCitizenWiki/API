<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Maps a pledge-store SKU to the shared {@see vehicle_sku} schema.
 *
 * @mixin PledgeStoreSku
 */
class PledgeStoreSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'source' => 'pledge_store',
            'cig_id' => $this->cig_id,
            'title' => $this->name,
            'url' => $this->url !== null ? sprintf('https://robertsspaceindustries.com%s', $this->url) : null,
            'price' => $this->native_price !== null ? intdiv((int) $this->native_price, 100) : null,
            'discounted_price' => $this->native_discounted !== null ? intdiv((int) $this->native_discounted, 100) : null,
            'is_warbond' => (bool) $this->is_warbond,
            'is_package' => (bool) $this->is_package,
            'available' => (bool) $this->stock_available,
            'imported_at' => $this->updated_at,
        ];
    }
}
