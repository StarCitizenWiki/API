<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComputeItemSetItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const array SET_PARTS = ['helmet', 'core', 'arms', 'legs'];

    public function __construct(private readonly int $gameVersionId) {}

    public function handle(): void
    {
        DB::table('game_item_set_items')
            ->whereIn('item_data_id', ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->select('id'))
            ->delete();

        ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereNotNull('class_name')
            ->chunkById(500, function (Collection $items): void {
                $parts = self::SET_PARTS;
                $candidates = [];

                foreach ($items as $itemData) {
                    $currentPart = null;

                    foreach ($parts as $part) {
                        if (str_contains($itemData->class_name, '_'.$part.'_')) {
                            $currentPart = $part;

                            break;
                        }
                    }

                    if ($currentPart === null) {
                        continue;
                    }

                    foreach ($parts as $part) {
                        if ($part === $currentPart) {
                            continue;
                        }

                        $candidates[] = [
                            'item_data_id' => $itemData->id,
                            'candidate_class_name' => Str::replaceFirst('_'.$currentPart.'_', '_'.$part.'_', $itemData->class_name),
                        ];
                    }
                }

                if ($candidates === []) {
                    return;
                }

                $classNames = array_column($candidates, 'candidate_class_name');

                $lookup = ItemData::query()
                    ->whereIn('class_name', $classNames)
                    ->where('game_version_id', $this->gameVersionId)
                    ->pluck('id', 'class_name');

                $inserts = [];

                foreach ($candidates as $candidate) {
                    $found = $lookup[$candidate['candidate_class_name']] ?? null;

                    if ($found !== null && $found !== $candidate['item_data_id']) {
                        $inserts[] = [
                            'item_data_id' => $candidate['item_data_id'],
                            'set_item_data_id' => $found,
                        ];
                    }
                }

                if ($inserts !== []) {
                    DB::table('game_item_set_items')->insert($inserts);
                }
            });
    }
}
