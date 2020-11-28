<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Download One Comm-Link Image
 */
class DownloadCommLinkImage extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Image
     */
    private Image $image;

    /**
     * Create a new job instance.
     *
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info(
            "Downloading Comm-Link Image {$this->image->name}",
            [
                'id' => $this->image->id,
                'src' => $this->image->src,
            ]
        );

        $localDirName = $this->image->dir ?? $this->generateLocalDirName();

        if (Storage::disk('comm_link_images')->exists(sprintf('%s/%s', $localDirName, $this->image->name))) {
            return;
        }

        $response = $this->makeClient()->get($this->image->url);

        if ($response->serverError()) {
            app('Log')::critical('Could not connect to RSI Website');

            $this->fail(new RequestException($response));

            return;
        }

        if ($response->clientError()) {
            app('Log')::info(
                "Could not download Comm-Link Image {$this->image->name}",
                [
                    'url' => $this->image->url,
                ]
            );

            $this->image->update(
                [
                    'local' => false,
                    'dir' => 'NOT_FOUND',
                ]
            );

            return;
        }

        $this->writeImage($response->body(), $localDirName);

        $this->image->update(
            [
                'local' => true,
                'dir' => $localDirName,
            ]
        );
    }

    /**
     * @return string
     */
    private function generateLocalDirName(): string
    {
        try {
            return bin2hex(random_bytes(7));
        } catch (Exception $e) {
            return Str::random(14);
        }
    }

    /**
     * Writes the image data to file
     *
     * @param string $data
     * @param string $folder
     */
    private function writeImage(string $data, string $folder): void
    {
        Storage::disk('comm_link_images')->put(
            sprintf('%s/%s', $folder, $this->image->name),
            $data
        );
    }
}
