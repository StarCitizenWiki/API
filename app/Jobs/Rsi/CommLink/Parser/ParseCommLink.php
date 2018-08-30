<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Parser;

use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Resort;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class ParseCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $id;
    private $file;

    /**
     * Create a new job instance.
     *
     * @param int    $id
     * @param string $file
     */
    public function __construct(int $id, string $file)
    {
        $this->id = $id;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $content = Storage::disk('comm_links')->get($this->fileName());
        $crawler = new Crawler($content);

        $content = $crawler->filter('#post')->first();

        try {
            $content->html();
        } catch (\InvalidArgumentException $e) {
            app('Log')::info("Comm-Link with id {$this->id} has no content");

            return;
        }


        $commentCount = 0;
        try {
            $commentCount = $crawler->filter('.comment-count')->first()->text();
        } catch (\InvalidArgumentException $e) {
            echo "Comm-Link with id {$this->id} has no Comments\n";
        }

        $createdAt = '2012-01-01 00:00:00';
        try {
            $createdAt = Carbon::parse(
                $crawler->filter('.title-section .details div:nth-of-type(3) p')->text()
            )->toDateTimeString();
        } catch (\InvalidArgumentException $e) {
            echo "Comm-Link with id {$this->id} has no Creation Date\n";
        }

        CommLink::updateOrCreate(
            [
                'cig_id' => $this->id,
            ],
            [
                'comment_count' => $commentCount,
                'file' => $this->file,
                'resort_id' => $this->getResort($crawler),
                'category_id' => $this->getCategory($crawler),
                'created_at' => $createdAt,
            ]
        );
    }

    private function fileName()
    {
        return "{$this->id}/{$this->file}";
    }

    private function getResort(Crawler $crawler)
    {
        try {
            $resort = $crawler->filter('.title-bar .title h2')->text();
        } catch (\InvalidArgumentException $e) {
            return Resort::find(1)->id;
        }

        return Resort::firstOrCreate(
            [
                'name' => $resort,
            ]
        )->id;
    }

    private function getCategory(Crawler $crawler)
    {
        try {
            $resort = $crawler->filter('.title-bar .title h1')->text();
        } catch (\InvalidArgumentException $e) {
            return Category::find(1)->id;
        }

        return Category::firstOrCreate(
            [
                'name' => $resort,
            ]
        )->id;
    }
}
