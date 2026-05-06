<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ComputeImageHash implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public function __construct(public readonly int $imageId)
    {
        $this->onConnection('database');
        $this->onQueue('expensive');
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->imageId),
        ];
    }

    public function handle(PdqHasher $hasher): void
    {
        $record = ImageHash::query()
            ->getConnection()
            ->table('comm_link_images')
            ->select(['id', 'src', 'local', 'dir'])
            ->where('id', $this->imageId)
            ->first();

        if ($record === null) {
            return;
        }

        $hasHash = ImageHash::query()
            ->where('comm_link_image_id', $record->id)
            ->whereNotNull('pdq_hash')
            ->exists();

        if ($hasHash) {
            return;
        }

        $response = Http::get($this->resolveImageUrl($record->src, (bool) $record->local, (string) $record->dir));

        if ($response->serverError()) {
            Log::warning('Comm-link image download failed with server error.', [
                'image_id' => $record->id,
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::info('Comm-link image download failed with client error.', [
                'image_id' => $record->id,
                'status' => $response->status(),
            ]);

            return;
        }

        $contentType = $response->header('Content-Type');
        if ($contentType !== null && ! str_starts_with($contentType, 'image/')) {
            Log::info('Comm-link image skipped because content type is not an image.', [
                'image_id' => $record->id,
                'content_type' => $contentType,
            ]);

            return;
        }

        try {
            $hashResult = $hasher->hashContents($response->body());
        } catch (RuntimeException $exception) {
            Log::info('Comm-link image hashing failed.', [
                'image_id' => $record->id,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        ImageHash::query()->updateOrCreate(
            [
                'comm_link_image_id' => $record->id,
            ],
            [
                'pdq_hash' => $hashResult->toBitString(),
                'pdq_quality' => $hashResult->quality,
            ]
        );

        ComputeSimilarImageIds::dispatch($record->id)
            ->onConnection('database')
            ->onQueue('expensive');
    }

    private function resolveImageUrl(string $src, bool $local, string $dir): string
    {
        if ($local) {
            $name = basename($src);

            return asset("storage/comm_link_images/{$dir}/{$name}");
        }

        $prefixes = ['/media', '/rsi', '/layoutscache', '/i/'];
        $baseUrl = 'https://media.robertsspaceindustries.com';

        foreach ($prefixes as $prefix) {
            if (str_starts_with($src, $prefix)) {
                $baseUrl = config('services.rsi_url');
                break;
            }
        }

        return $baseUrl.$src;
    }
}
