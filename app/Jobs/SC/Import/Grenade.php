<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class Grenade extends AbstractItemCreationJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->loadLabels();
        try {
            $parser = new \App\Services\Parser\SC\Grenade($this->filePath, $this->labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\Char\Grenade $model */
        \App\Models\SC\Char\Grenade::query()->withoutGlobalScopes()->updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'aoe' => $item['aoe'] ?? null,
            'damage_type' => $item['damage_type'] ?? null,
            'damage' => $item['damage'] ?? null,
        ]);
    }
}
