<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use App\Services\Parser\StarCitizenUnpacked\WeaponAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WeaponAttachments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/v2/items');

        collect($files)
            ->filter(function (string $file) {
                return strpos($file, '-raw.json') !== false;
            })
            ->map(function (string $file) use ($labels, $manufacturers) {
                return (new WeaponAttachment($file, $labels, $manufacturers))
                    ->getData();
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->filter(function ($item) {
                return Item::query()->where('uuid', $item['uuid'])->exists();
            })
            ->each(function ($item) {
                if ($item['name'] === '<= PLACEHOLDER =>') {
                    return;
                }

                if ($item['attachment_point'] === null && $item['type'] === 'Utility') {
                    $item['attachment_point'] = 'Utility';
                }

                if ($item['attachment_point'] === 'Optic') {
                    $item['item_type'] = 'Scope';
                }

                /** @var Attachment $model */
                $model = Attachment::updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'attachment_name' => trim($item['name'], '  '),
                    'position' => $item['attachment_point'] ?? null,
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
            });
    }
}
