<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class TrackApiRouteCall implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public array $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Http::withHeaders([
            'User-Agent' => $this->request['user-agent'],
            'X-Forwarded-For' => $this->request['forwarded-for'],
        ])
            ->timeout(10)
            ->retry(5)
            ->post(sprintf('%s/api/event', config('services.plausible.domain')), [
                'name' => 'pageview',
                'url' => $this->request['url'],
                'domain' => parse_url(config('app.url'))['host'],
            ]);
    }
}
