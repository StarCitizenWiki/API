<?php declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\ImageHash\Implementations\PerceptualHash2;
use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Exception\NotReadableException;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class CreateImageHash extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Image $image;
    private ImageHash $perceptionHasher;
    private ImageHash $differenceHasher;
    private ImageHash $averageHasher;

    /**
     * Data used if Hash could not be created
     */
    private const NOT_FOUND_HASH = [
        'perceptual_hash' => 'DEADBEEF',
        'p_hash_1' => 0,
        'p_hash_2' => 0,

        'difference_hash' => 'DEADBEEF',
        'd_hash_1' => 0,
        'd_hash_2' => 0,

        'average_hash' => 'DEADBEEF',
        'a_hash_1' => 0,
        'a_hash_2' => 0,
    ];

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
        }

        $file = $this->image->getLocalOrRemoteUrl();
        $url = $file;

        if (!$this->image->local) {
            $file = $this->downloadFile($file);
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

        if ($perception === '' || $difference === '' || $average === '') {
            app('Log')::debug("Hash for {$url} is empty.", [$url]);
            $this->image->hash()->create(self::NOT_FOUND_HASH);

            return;
        }

        $perceptionData = $this->splitHexString($perception);
        $differenceData = $this->splitHexString($difference);
        $averageData = $this->splitHexString($average);

        $this->image->hash()->create(
            [
                'perceptual_hash' => $perception,
                'p_hash_1' => $perceptionData[0],
                'p_hash_2' => $perceptionData[1],

                'difference_hash' => $difference,
                'd_hash_1' => $differenceData[0],
                'd_hash_2' => $differenceData[1],

                'average_hash' => $average,
                'a_hash_1' => $averageData[0],
                'a_hash_2' => $averageData[1],
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
        $this->initClient();

        try {
            $response = self::$client->get($url);
        } catch (ServerException | ConnectException | TooManyRedirectsException $e) {
            app('Log')::debug('Download of Comm-Link image failed. Retrying in 300 seconds.', [$url, $e->getCode()]);

            $this->release(300);

            return null;
        } catch (ClientException $e) {
            if (!$selfCall && $e->getCode() === 404) {
                $url = str_replace('/source/', '/post/', $url);

                app('Log')::debug('Retrying download with smaller version.', [$url]);

                // Retry with smaller version
                return $this->downloadFile($url, true);
            }

            app('Log')::info("Download of Comm-Link image resulted in code {$e->getCode()}", [$url]);

            return null;
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param string $hex
     *
     * @return array
     */
    private function splitHexString(string $hex): array
    {
        if ($hex === '') {
            return [0, 0];
        }

        $hex = str_split($hex, strlen($hex) / 2);

        return array_map('hexdec', $hex);
    }
}
