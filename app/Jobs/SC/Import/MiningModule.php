<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Services\Parser\StarCitizenUnpacked\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class MiningModule implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\StarCitizenUnpacked\MiningModule($this->filePath, $labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\ItemSpecification\MiningModule\MiningModule $model */
        $model = \App\Models\SC\ItemSpecification\MiningModule\MiningModule::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'type' => $item['type'] ?? null,
        ]);

        if (!empty($item['description'])) {
            $model->translations()->updateOrCreate([
                'locale_code' => 'en_EN',
            ], [
                'translation' => $item['description'],
            ]);
        }

        collect($item['modifiers'])
            ->filter()
            ->each(function ($item, $key) use ($model) {
                if ($item === null) {
                    return;
                }
                $model->modifiers()->updateOrCreate([
                    'name' => $key,
                ], [
                    'modifier' => $item,
                ]);
            });
    }
}
