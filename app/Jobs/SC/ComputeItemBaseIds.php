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
                    ->where('type', $item->type)
                    ->whereIn('class_name', $baseClassChecks)
                    ->first();

                $item->update([
                    'base_id' => $baseModel?->id,
                ]);
            });
        });
    }
}
