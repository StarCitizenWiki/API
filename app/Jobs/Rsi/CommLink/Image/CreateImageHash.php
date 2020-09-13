<?php declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Image;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Rsi\CommLink\Image\Image;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

class CreateImageHash extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Image $image;
    private ImageHash $hasher;

    /**
     * Create a new job instance.
     *
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
        $this->hasher = new ImageHash(new DifferenceHash());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $file = $this->image->getLocalOrRemoteUrl();

        if (!$this->image->local) {
            $file = $this->downloadFile($file);
        }

        $hash = $this->hasher->hash($file);

        $query = DB::query()->raw(
            'INSERT INTO `comm_link_image_hashes` (`comm_link_image_id`, `hash`, `created_at`, `updated_at`) VALUES 
                                            ('.$this->image->id.', 
                                            b\''.$hash->toBits().'\',
                                            \''.Carbon::now()->toDateTimeString().'\',
                                            \''.Carbon::now()->toDateTimeString().'\')'
        )->getValue();
        DB::insert($query);
    }

    /**
     * Downloads a file and returns the content
     *
     * @param string $url
     *
     * @return string|void
     */
    private function downloadFile(string $url)
    {
        $this->initClient();

        try {

            $response = self::$client->get($url);
        } catch (GuzzleException $e) {
            $this->release(300);

            return;
        }

        return $response->getBody()->getContents();
    }
}
