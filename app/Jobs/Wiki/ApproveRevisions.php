<?php

namespace App\Jobs\Wiki;

use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use ErrorException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class ApproveRevisions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetWikiCsrfToken;

    private array $pageTitles;
    private string $csrfToken = '';

    /**
     * Create a new job instance.
     *
     * @param array $pageTitles
     */
    public function __construct(array $pageTitles)
    {
        $this->pageTitles = $pageTitles;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->requestCsrfToken();
        $ids = $this->getRevisionIDs();
        $ids = collect($ids['pages'] ?? [])
            ->filter(
                function ($page) {
                    // Only approve new pages
                    return isset($page['new']);
                }
            )
            ->map(
                function ($page) {
                    return $page['revisions'] ?? [];
                }
            )
            ->map(
                function ($revisions) {
                    return Arr::first($revisions, null, []);
                }
            )
            ->map(
                function ($revision) {
                    return $revision['revid'] ?? 0;
                }
            )
            ->filter(
                function ($id) {
                    return $id > 0;
                }
            );

        $this->approveRevisions($ids);
    }

    /**
     * Requests an CSRF Token from the Wiki
     */
    private function requestCsrfToken(): void
    {
        try {
            $token = $this->getCsrfToken('services.wiki_approve_revs');
        } catch (ErrorException $e) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Token retrieval failed',
                    $e->getMessage()
                )
            );

            $this->release(300);

            return;
        }

        if ($token === null) {
            $this->release(300);

            return;
        }

        $this->csrfToken = $token;
    }

    /**
     * Revision ids from page titles
     *
     * @return array Page revision ids
     */
    private function getRevisionIDs(): array
    {
        $revisions = MediaWikiApi::query()
            ->formatVersion(2)
            ->json()
            ->prop('revisions')
            ->prop('info')
            ->titles(implode('|', $this->pageTitles))
            ->addParam('rvprop', 'ids')
            ->request();

        if ($revisions->hasErrors()) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Revision retrieval failed',
                    $revisions->getErrors()['code'] ?? ''
                )
            );

            $this->release(300);
        }

        return $revisions->getQuery();
    }

    /**
     * Approve revisions
     *
     * @param Collection $ids
     */
    private function approveRevisions(Collection $ids): void
    {
        $ids->each(
            function ($id) {
                $response = MediaWikiApi::action('approve', 'POST')
                    ->withAuthentication()
                    ->csrfToken($this->csrfToken)
                    ->addParam('revid', $id)
                    ->request();

                if ($response->hasErrors()) {
                    app('Log')::error(
                        sprintf(
                            'Could not approve revision %s. Message: %s',
                            $id,
                            json_encode($response->getErrors())
                        )
                    );
                }
            }
        );
    }
}
