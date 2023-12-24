<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use App\Services\ImageHash\Implementations\PDQHash\PDQHash;
use App\Services\ImageHash\Implementations\PDQHasher;
use App\Services\ImageHash\Implementations\PerceptualHash2;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class CreateImageHash extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Data used if Hash could not be created
     */
    private const NOT_FOUND_HASH = [
        'perceptual_hash' => 0xDEADBEEF,
        'difference_hash' => 0xDEADBEEF,
        'average_hash' => 0xDEADBEEF,
    ];
    private Image $image;
    private ImageHash $perceptionHasher;
    private ImageHash $differenceHasher;
    private ImageHash $averageHasher;

    /**
     * Create a new job instance.
     *
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;

        $this->perceptionHasher = new ImageHash(new PerceptualHash2(32));
        $this->differenceHasher = new ImageHash(new DifferenceHash());
        $this->averageHasher = new ImageHash(new AverageHash());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            app('Log')::error('Required extension "GD" or "Imagick" not available.');

            $this->fail('Required extension "GD" or "Imagick" not available.');
            return;
        }

        if (!str_contains($this->image->metadata->mime, 'image')) {
            $this->delete();
            return;
        }

        if ($this->image->hash !== null && $this->image->hash->exists && $this->image->hash->pdq_hash1 !== null) {
            $this->delete();

            return;
        }

        $file = $this->image->getLocalOrRemoteUrl();
        $url = $file;

        if (!$this->image->local) {
            if (Storage::disk('comm_link_images')->exists("{$this->image->dir}/{$this->image->name}")) {
                $this->image->update(['local' => true]);
            } else {
                $this->fail('File not local');
                return;
            }
        }

        // 4xx Error
        if ($file === null) {
            $this->image->hash()->create(self::NOT_FOUND_HASH);

            return;
        }

        try {
            $hash = $this->perceptionHasher->hash($file);
        } catch (NotReadableException $e) {
            app('Log')::info("Image {$url} is not readable", [$url]);
            $this->fail($e);

            return;
        }

        $perception = $hash->toHex();
        $difference = $this->differenceHasher->hash($file)->toHex();
        $average = $this->averageHasher->hash($file)->toHex();

        $path = storage_path("app/public/comm_link_images/{$this->image->dir}/{$this->image->name}");

        try {
            [$hash, $quality] = PDQHasher::computeHashAndQualityFromFilename($path, false, false, true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
            return;
        }

        /** @var PDQHash $hash */

        $hash = $hash->to64BitStrings();

        if ($perception === '' || $difference === '' || $average === '') {
            app('Log')::debug("Hash for {$url} is empty.", [$url]);
            $this->image->hash()->create(self::NOT_FOUND_HASH);

            return;
        }

        $this->image->hash()->updateOrCreate(
            [
                'perceptual_hash' => hex2bin($perception),
                'difference_hash' => hex2bin($difference),
                'average_hash' => hex2bin($average),
                'pdq_hash1' => hex2bin($hash[0]),
                'pdq_hash2' => hex2bin($hash[1]),
                'pdq_hash3' => hex2bin($hash[2]),
                'pdq_hash4' => hex2bin($hash[3]),
                'pdq_quality' => $quality,
            ]
        );
    }

    /**
     * Downloads a file and returns the content
     *
     * @param string $url
     *
     * @param bool   $selfCall Don't retry indefinitely
     *
     * @return string|null
     */
    private function downloadFile(string $url, bool $selfCall = false): ?string
    {
        $response = $this->makeClient()->get($url);

        if ($response->serverError()) {
            app('Log')::debug(
                'Download of Comm-Link image failed. Retrying in 300 seconds.',
                [$url, $response->status()]
            );

            $this->release(300);

            return null;
        }

        if ($response->clientError()) {
            if (!$selfCall && $response->status() === 404) {
                $url = str_replace('/source/', '/post/', $url);

                app('Log')::debug('Retrying download with smaller version.', [$url]);

                // Retry with smaller version
                return $this->downloadFile($url, true);
            }

            app('Log')::info("Download of Comm-Link image resulted in code {$response->status()}", [$url]);

            return null;
        }

        return $response->body();
    }
}
