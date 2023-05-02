<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class WeaponAttachment implements ShouldQueue
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\WeaponAttachment($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        if ($item === null) {
            return;
        }

        /** @var Attachment $model */
        $model = Attachment::updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'attachment_name' => $item['name'],
            'position' => $item['attachment_point'],
            'size' => $item['size'],
            'grade' => $item['grade'],
            'type' => $item['item_type'] ?? $item['type'],
            'version' => config('api.sc_data_version'),
        ]);

        if ($item['magnification'] !== null) {
            $model->optics()->updateOrCreate([
                'magnification' => $item['magnification'],
                'type' => $item['type'] ?? '',
            ]);
        }

        if ($item['capacity'] !== null) {
            $model->magazine()->updateOrCreate([
                'capacity' => $item['capacity'],
            ]);
        }

        $model->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $item['description'] ?? '',
        ]);
    }
}
