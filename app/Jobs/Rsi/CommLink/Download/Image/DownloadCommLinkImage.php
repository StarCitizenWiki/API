<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Download\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
     * @var \App\Models\Rsi\CommLink\Image\Image
     */
    private $image;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Rsi\CommLink\Image\Image $image
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
    public function handle()
    {
        app('Log')::info(
            "Downloading Comm-Link Image {$this->image->name}",
            [
                'id' => $this->image->id,
                'src' => $this->image->src,
            ]
        );

        $this->initClient();

        try {
            $response = $this->client->get($this->image->url);
        } catch (ConnectException | ServerException $e) {
            app('Log')::critical(
                'Could not connect to RSI Website',
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        } catch (ClientException $e) {
            app('Log')::info(
                "Could not download Comm-Link Image {$this->image->name}",
                [
                    'url' => $this->image->url,
                ]
            );

            $this->image->update(
                [
                    'local' => true,
                    'dir' => 'NOT_FOUND',
                ]
            );

            return;
        }

        $localDirName = $this->image->dir;
        if (null === $this->image->dir) {
            $localDirName = $this->generateLocalDirName();
        }

        Storage::disk('comm_link_images')->put(
            sprintf('%s/%s', $localDirName, $this->image->name),
            $response->getBody()
        );

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
        } catch (\Exception $e) {
            return str_random(14);
        }
    }
}
