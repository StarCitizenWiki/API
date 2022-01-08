<?php

declare(strict_types=1);

namespace App\Jobs\Transcript;

use App\Models\Transcript\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class ImportMetadata implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private bool $chunkAll;

    /**
     * @param bool $chunkAll Load 2000 metadata per chunk and sort by date
     */
    public function __construct(bool $chunkAll = false)
    {
        $this->chunkAll = $chunkAll;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        collect(Storage::disk('transcripts')->allFiles())
            ->filter(function (string $path) {
                return substr($path, -5) === '.json';
            })
            ->chunk($this->chunkAll ? 2000 : 10)
            ->each(function (Collection $chunk) {
                $chunk
                    ->map(function (string $path) {
                        try {
                            $content = json_decode(
                                File::get(Storage::disk('transcripts')->path($path)),
                                true,
                                512,
                                JSON_THROW_ON_ERROR
                            );
                        } catch (FileNotFoundException | JsonException $e) {
                            app('Log')::warning($e->getMessage());
                            return null;
                        }

                        $thumbnail = null;
                        if (count($content['thumbnails']) > 1) {
                            $thumbnail = array_pop($content['thumbnails']);
                            if ($thumbnail !== null) {
                                $thumbnail = explode('?', $thumbnail['url']);
                                $thumbnail = $thumbnail[0];
                            }
                        }

                        $filename = $content['_filename'] ?? null;
                        if ($filename !== null) {
                            $filename = explode('/', $filename);
                            $filename = array_pop($filename);
                        }

                        return [
                            'youtube_id' => $content['id'],
                            'title' => $content['title'],
                            'playlist_name' => $content['playlist_title'] ?? null,
                            'upload_date' => Carbon::parse(str_replace('.', '', $content['upload_date']))->toDateString(),
                            'runtime' => $content['duration'],
                            'thumbnail' => $thumbnail,
                            'youtube_description' => $content['description'],
                            'filename' => $filename,
                        ];
                    })
                    ->filter(function ($in) {
                        return !empty($in);
                    })
                    ->sortBy('upload_date')
                    ->each(function (array $data) {
                        $id = $data['youtube_id'];
                        unset($data['youtube_id']);

                        Transcript::query()->firstOrCreate([
                            'youtube_id' => $id,
                        ], $data);
                    });
            });
    }
}
