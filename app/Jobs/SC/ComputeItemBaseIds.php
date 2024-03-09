<?php

namespace App\Jobs\SC;

use App\Models\SC\Item\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
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
                $class = explode('_', $item->class_name);
                array_pop($class);
                $class = implode('_', $class);
                $classAlt = '';

                $idEnd = strpos($item->class_name, '01');
                if ($idEnd !== false) {
                    $classAlt = substr($class, 0, $idEnd + 2);
                }

                $baseModel = Item::query()
                    ->where('uuid', '<>', $item->uuid)
                    ->where('type', $item->type)
                    ->where(function (Builder $query) use ($class, $classAlt) {
                        $query->where('class_name', $class)
                            ->orWhere('class_name', sprintf('%s_01', $class));

                        if (! empty($classAlt)) {
                            $query->orWhere('class_name', $classAlt);
                        }
                    })
                    ->first();

                if ($baseModel !== null) {
                    $item->update([
                        'base_id' => $baseModel->id,
                    ]);
                }
            });
        });
    }
}
