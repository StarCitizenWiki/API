<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateImageMetadatum extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $url = $this->image->url;

        $response = $this->makeClient()->head($url);

        if ($response->serverError()) {
            app('Log')::debug('Header request failed. Retrying in 300 seconds.', [$url, $response->status()]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            app('Log')::info("Header request resulted in code {$response->status()}", [$url]);

            if ($this->image->metadata === null) {
                $this->image->metadata()->create(
                    [
                        'mime' => 'undefined',
                        'size' => 0,
                        'last_modified' => '0001-01-01 00:00:00',
                    ]
                );
            }

            return;
        }

        $this->saveMetadata($response);
    }

    /**
     * Saves response data as metadata
     *
     * @param Response $response
     */
    private function saveMetadata(Response $response): void
    {
        $data = [
            'mime' => $response->header('content-type'),
            'size' => $response->header('content-length'),
            'last_modified' => Carbon::parse($response->header('last-modified'))->toDateTimeString(),
        ];

        foreach ($data as $key => $datum) {
            if ($datum === '') {
                unset($data[$key]);
            }
        }

        if ($this->image->metadata === null) {
            $this->image->metadata()->create($data);
        }
    }
}
