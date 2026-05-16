<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Vehicle;

use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSkuHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillPledgeStoreHistory extends Command
{
    protected $signature = 'pledge-store:backfill-history';

    protected $description = 'Backfill pledge_store_sku_history from legacy shipmatrix_vehicle_skus data';

    public function handle(): int
    {
        $this->info('Backfilling pledge store history from shipmatrix_vehicle_skus...');

        $newSkus = PledgeStoreSku::query()
            ->where('product_id', 72)
            ->get();

        $newLookup = collect();

        foreach ($newSkus as $sku) {
            $key = trim($sku->name);

            if (! $newLookup->has($key)) {
                $newLookup->put($key, collect());
            }

            $newLookup->get($key)->push($sku);
        }

        $oldSkus = DB::table('shipmatrix_vehicle_skus')
            ->join('shipmatrix_vehicles', 'shipmatrix_vehicle_skus.vehicle_id', '=', 'shipmatrix_vehicles.id')
            ->select([
                'shipmatrix_vehicles.name as vehicle_name',
                'shipmatrix_vehicle_skus.title as sku_title',
                'shipmatrix_vehicle_skus.price',
                'shipmatrix_vehicle_skus.available',
                'shipmatrix_vehicle_skus.created_at',
            ])
            ->orderBy('shipmatrix_vehicle_skus.created_at')
            ->get();

        $this->info("Found {$oldSkus->count()} legacy SKUs, {$newSkus->count()} pledge store ship SKUs.");

        $historyCreated = 0;
        $skipped = 0;
        $noMatch = 0;

        foreach ($oldSkus as $old) {
            $name = trim($old->vehicle_name);
            $isWarbond = str_contains($old->sku_title, 'Warbond');
            $group = $newLookup->get($name);

            if ($group === null) {
                $noMatch++;

                continue;
            }

            $newSku = $group->first(fn (PledgeStoreSku $s): bool => $s->is_warbond === $isWarbond)
                ?? $group->first();

            if ($newSku === null) {
                $noMatch++;

                continue;
            }

            $oldPriceCents = $old->price * 100;
            $tags = $this->mapSkuTitleToTags($old->sku_title);

            // Skip if this would duplicate an existing history entry
            $alreadyExists = PledgeStoreSkuHistory::query()
                ->where('pledge_store_sku_id', $newSku->id)
                ->where('created_at', $old->created_at)
                ->where('data->nativePrice->amount', $oldPriceCents)
                ->where('data->stock->available', (bool) $old->available)
                ->exists();

            if ($alreadyExists) {
                $skipped++;

                continue;
            }

            // Skip if price matches current state and there's already a history entry
            // at or before this timestamp (no new information)
            if ($oldPriceCents === $newSku->native_price) {
                $earlierHistory = PledgeStoreSkuHistory::query()
                    ->where('pledge_store_sku_id', $newSku->id)
                    ->where('created_at', '<=', $old->created_at)
                    ->exists();

                if ($earlierHistory) {
                    $skipped++;

                    continue;
                }
            }

            PledgeStoreSkuHistory::query()->create([
                'pledge_store_sku_id' => $newSku->id,
                'data' => [
                    'nativePrice' => [
                        'amount' => $oldPriceCents,
                        'discounted' => null,
                        'discountDescription' => null,
                    ],
                    'stock' => [
                        'available' => (bool) $old->available,
                        'unlimited' => (bool) $old->available,
                        'qty' => 0,
                        'backOrder' => false,
                        'backOrderQty' => 0,
                        'level' => (bool) $old->available ? 'high' : null,
                    ],
                    'tags' => collect($tags)->map(fn (string $tag) => ['name' => $tag])->values()->toArray(),
                    'source' => 'backfill',
                ],
                'created_at' => $old->created_at,
            ]);

            $historyCreated++;
        }

        $this->info("Backfill complete: {$historyCreated} history entries created, {$skipped} skipped (duplicate or no new info), {$noMatch} no match.");

        Log::info('Pledge store history backfill completed', [
            'created' => $historyCreated,
            'skipped' => $skipped,
            'no_match' => $noMatch,
        ]);

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function mapSkuTitleToTags(string $title): array
    {
        $tags = [];

        if (str_contains($title, 'Warbond')) {
            $tags[] = 'Warbond';
        }

        if (str_contains($title, 'Subscribers')) {
            $tags[] = 'Subscriber Exclusive';
        }

        if (str_contains($title, 'BIS')) {
            $tags[] = 'Best In Show';
        }

        return $tags;
    }
}
