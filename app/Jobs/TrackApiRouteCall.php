<?php

declare(strict_types=1);

namespace App\Jobs;

use Dingo\Api\Http\Request;
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

    public Request $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request)
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
            'User-Agent' => $this->request->userAgent() ?? 'Star Citizen Wiki API',
            'X-Forwarded-For' => $this->request->header('X-Forwarded-For', '127.0.0.1'),
        ])
            ->timeout(10)
            ->retry(5)->dd()
            ->post(sprintf('%s/api/event', config('services.plausible.domain')), [
                'name' => 'pageview',
                'url' => $this->request->fullUrl(),
                'domain' => parse_url(config('app.url'))['host'],
            ]);
    }
}
