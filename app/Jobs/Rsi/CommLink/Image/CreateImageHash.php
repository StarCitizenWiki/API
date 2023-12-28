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
use Symfony\Component\Process\Process;

class CreateImageHash extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $deleteTempFile = false;
    private $tempFileUrl;

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
     * Delete possible temp files when done
     */
    public function __destruct()
    {
        if ($this->deleteTempFile) {
            File::delete($this->tempFileUrl);
        }
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

        if ($this->image->hash !== null && $this->image->hash->exists && $this->image->hash->pdq_hash1 !== null) {
            $this->delete();

            return;
        }

        if ($this->image->local) {
            $fileUrl = $this->image->local_path;
        } else {
            $fileUrl = $this->image->getLocalOrRemoteUrl();
        }

        if (str_contains($this->image->metadata->mime, 'video')) {
            if (!$this->image->local) {
                $this->fail('Can\'t extract frame from remote file.');

                return;
            }

            $fileUrl = $this->saveVideoFrame();
            if ($fileUrl === null) {
                $this->fail(sprintf('Could not extract frame from video %s', $this->image->name));

                return;
            }

            $this->deleteTempFile = true;
            $this->tempFileUrl = $fileUrl;
        }

        $pdqFromStream = false;
        if (!$this->image->local) {
            if (Storage::disk('comm_link_images')->exists("{$this->image->dir}/{$this->image->name}")) {
                $this->image->update(['local' => true]);
            } else {
                $pdqFromStream = true;
                $fileUrl = $this->downloadFile($fileUrl);
            }
        }

        // 4xx Error
        if ($fileUrl === null) {
            return;
        }

        try {
            $hash = $this->perceptionHasher->hash($fileUrl);
        } catch (NotReadableException $e) {
            app('Log')::info("Image $fileUrl is not readable", [$fileUrl]);
            $this->fail($e);

            return;
        }

        $perception = $hash->toHex();
        $difference = $this->differenceHasher->hash($fileUrl)->toHex();
        $average = $this->averageHasher->hash($fileUrl)->toHex();

        try {
            [$hash, $quality] = PDQHasher::computeHashAndQualityFromFilename(
                $fileUrl,
                true,
                $pdqFromStream
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        /** @var PDQHash $hash */
        $hash = $hash->to64BitStrings();

        if ($perception === '' || $difference === '' || $average === '') {
            app('Log')::debug("Hash for $fileUrl is empty.", [$fileUrl]);

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

    /**
     * Use FFMPEG to retrieve a frame from second 1
     *
     * @return string|null
     */
    private function saveVideoFrame(): ?string
    {
        $fp = tmpfile();
        $path = stream_get_meta_data($fp)['uri'];
        fclose($fp);
        $pathExt = $path . '.jpg';

        $proc = new Process([
            '/usr/bin/ffmpeg',
            '-i',
            $this->image->local_path,
            '-an',
            '-ss',
            '1',
            '-y',
            '-f',
            'mjpeg',
            $pathExt
        ]);

        $proc->setTimeout(120);

        $code = $proc->run();

        return $code === 0 ? $pathExt : null;
    }
}
