<?php

namespace App\Jobs\SC;

use App\Models\SC\Item\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ComputeItemBaseIds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Item::query()->chunk(250, function (Collection $items) {
            $items->each(function (Item $item) {
                // No '01,02, etc.' found, we currently assume that this means no base variants
                if (preg_match('/[a-z-_]+_0\d/i', $item->class_name) === false) {
                    return;
                }

                $idEnd = strpos($item->class_name, '0');
                $class = substr($item->class_name, 0, $idEnd + 2);
                $baseClassChecks = [
                    $class,
                    $class.'_01',
                    $class.'_01_01',
                ];

                $baseModel = Item::query()
                    ->where('uuid', '<>', $item->uuid)
                    ->where('type', $item->type);

                // Special backpack handling, they differ in their "set" ids (we assume) but share the same set
                if ($item->type === 'Char_Armor_Backpack') {
                    $baseClass = substr($item->class_name, 0, $idEnd - 1);

                    if (str_ends_with($item->name, 'Backpack') && ! str_contains($item->name, '"Expo"')) {
                        $baseClass = '<>'; // Don't match anything
                    }

                    $baseModel
                        ->where('class_name', 'LIKE', $baseClass.'%')
                        ->orderBy('name');
                } else {
                    $baseModel->whereIn('class_name', $baseClassChecks);
                }

                $baseModel = $baseModel->first();

                $item->update([
                    'base_id' => $baseModel?->id,
                ]);
            });
        });
    }
}
