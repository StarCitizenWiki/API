<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportItemPrices implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    private const string API_URL = 'https://api.uexcorp.uk/2.0/items_prices_all';

    public function __construct(
        private readonly int $gameVersionId
    ) {}

    public function handle(): void
    {
        $response = Http::timeout(60)->get(self::API_URL);

        if (! $response->successful()) {
            Log::error('UEX API request failed', [
                'status' => $response->status(),
                'game_version_id' => $this->gameVersionId,
            ]);

            $exception = $response->toException();
            if ($exception !== null) {
                $this->fail($exception);
            }

            return;
        }

        $data = $response->json('data', []);

        if (! is_array($data)) {
            Log::error('UEX API returned invalid data format', [
                'game_version_id' => $this->gameVersionId,
            ]);

            return;
        }

        $this->processPrices($data);
    }

    private function processPrices(array $apiData): void
    {
        $grouped = collect($apiData)->groupBy('item_uuid');

        $uuids = $grouped->keys()->filter()->unique()->toArray();

        if ($uuids === []) {
            Log::warning('UEX API returned no item UUIDs');

            return;
        }

        $items = Item::query()
            ->whereIn('uuid', $uuids)
            ->pluck('id', 'uuid');

        $missingUuids = collect($uuids)->diff($items->keys());
        if ($missingUuids->isNotEmpty()) {
            Log::warning('UEX items not found in database', [
                'count' => $missingUuids->count(),
                'uuids' => $missingUuids->take(10)->toArray(),
            ]);
        }

        $itemDataCollection = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('item_id', $items->values())
            ->get()
            ->keyBy('item_id');

        $updatedCount = 0;

        foreach ($grouped as $uuid => $prices) {
            $itemId = $items->get($uuid);

            if ($itemId === null) {
                continue;
            }

            $itemData = $itemDataCollection->get($itemId);

            if ($itemData === null) {
                Log::debug('ItemData not found for item', [
                    'item_id' => $itemId,
                    'game_version_id' => $this->gameVersionId,
                ]);

                continue;
            }

            $pricesData = collect($prices)
                ->map(fn (array $p): array => [
                    'terminal_id' => $p['id_terminal'],
                    'terminal_name' => $p['terminal_name'],
                    'price_buy' => $p['price_buy'],
                    'price_sell' => $p['price_sell'],
                    'date_updated' => Carbon::createFromTimestamp((int) $p['date_modified'])->toIso8601String(),
                ])
                ->unique('terminal_id')
                ->values()
                ->toArray();

            $itemData->uex_prices = $pricesData;
            $itemData->save();

            $updatedCount++;
        }

        Log::info('UEX prices imported', [
            'count' => $updatedCount,
            'game_version_id' => $this->gameVersionId,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('UEX price import job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }
}
