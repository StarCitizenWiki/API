<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateImageMetadatum implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(public readonly int $imageId) {}

    public function handle(): void
    {
        $image = Image::query()->with('metadata')->find($this->imageId);

        if ($image === null) {
            return;
        }

        $response = Http::timeout(30)->head($image->url);

        if ($response->serverError()) {
            Log::warning('Comm-Link image metadata request failed with server error.', [
                'image_id' => $image->id,
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::info('Comm-Link image metadata request failed with client error.', [
                'image_id' => $image->id,
                'status' => $response->status(),
            ]);

            if ($image->metadata === null || $image->metadata->mime === 'undefined') {
                $image->metadata()->updateOrCreate([
                    'comm_link_image_id' => $image->id,
                ], [
                    'mime' => 'undefined',
                    'size' => 0,
                    'last_modified' => '0001-01-01 00:00:00',
                ]);
            }

            return;
        }

        $data = [
            'mime' => $response->header('content-type'),
            'size' => $response->header('content-length'),
            'last_modified' => $response->header('last-modified'),
        ];

        if ($data['last_modified'] !== null) {
            try {
                $data['last_modified'] = Carbon::parse($data['last_modified'])->toDateTimeString();
            } catch (Exception $exception) {
                $data['last_modified'] = null;
            }
        }

        $data = array_filter($data, static fn ($value) => $value !== null && $value !== '');

        $image->metadata()->updateOrCreate([
            'comm_link_image_id' => $image->id,
        ], $data);
    }
}
