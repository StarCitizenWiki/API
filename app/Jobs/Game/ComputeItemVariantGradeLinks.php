<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Persist the grade axis: peers that share an item's classification, manufacturer and size but sit at other grades.
 */
class ComputeItemVariantGradeLinks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $gameVersionId) {}

    public function handle(): void
    {
        DB::table('game_item_variant_grade_items')
            ->whereIn('item_data_id', ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->select('id'))
            ->delete();

        $cells = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('classification', 'LIKE', 'Ship.%')
            ->whereNot('classification', 'Ship.Paints')
            ->where('is_player_relevant', true)
            ->get(['id', 'classification', 'manufacturer_id', 'size', 'grade'])
            ->groupBy(fn (ItemData $item): string => $item->classification.'|'.$item->manufacturer_id.'|'.$item->size);

        $inserts = [];

        foreach ($cells as $members) {
            $grades = $members
                ->pluck('grade')
                ->filter(fn ($grade): bool => $grade !== null)
                ->unique()
                ->count();

            if ($grades < 2 || $members->count() !== $grades) {
                continue;
            }

            foreach ($members as $member) {
                foreach ($members as $peer) {
                    if ($member->id !== $peer->id) {
                        $inserts[] = [
                            'item_data_id' => $member->id,
                            'grade_item_data_id' => $peer->id,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('game_item_variant_grade_items')->insert($chunk);
        }
    }
}
