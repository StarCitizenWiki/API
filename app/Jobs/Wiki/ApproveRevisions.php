<?php

declare(strict_types=1);

namespace App\Jobs\Wiki;

use App\Services\WrappedWiki;
use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
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
    private string $token = '';
    private bool $onlyApproveNew;
    private bool $resolveRedirects;

    /**
     * Create a new job instance.
     *
     * @param array $pageTitles
     * @param bool $onlyApproveNew True if only recently created pages shall be approved
     * @param bool $resolveRedirects True if a given title should be checked against redirects
     */
    public function __construct(array $pageTitles, bool $onlyApproveNew = true, bool $resolveRedirects = false)
    {
        $this->pageTitles = $pageTitles;
        $this->onlyApproveNew = $onlyApproveNew;
        $this->resolveRedirects = $resolveRedirects;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->requestCsrfToken();
        $ids = $this->getRevisionIDs();
        $ids = collect($ids['pages'] ?? [])
            ->filter(
                function ($page) {
                    if ($this->onlyApproveNew === true) {
                        // Only approve new pages
                        return isset($page['new']);
                    }

                    return true;
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

        $this->token = $token;
    }

    /**
     * Revision ids from page titles
     *
     * @return array Page revision ids
     */
    private function getRevisionIDs(): array
    {
        $titles = $this->pageTitles;

        try {
            if ($this->resolveRedirects === true) {
                $titles = collect($this->pageTitles)->map(function ($title) {
                    return WrappedWiki::getRedirectTitle($title);
                })->toArray();
            }

            $revisions = MediaWikiApi::query()
                ->formatVersion(2)
                ->json()
                ->prop('revisions')
                ->prop('info')
                ->titles(implode('|', $titles))
                ->addParam('rvprop', 'ids')
                ->request();
        } catch (GuzzleException $e) {
            $this->release(300);

            return [];
        }

        if ($revisions->hasErrors()) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Revision retrieval failed',
                    $revisions->getErrors()['code'] ?? ''
                )
            );

            $this->release(300);

            return [];
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
                try {
                    $response = MediaWikiApi::action('approve', 'POST')
                        ->withAuthentication()
                        ->csrfToken($this->token)
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
                } catch (GuzzleException $e) {
                    app('Log')::error(
                        sprintf(
                            'Could not approve revision %s. Message: %s',
                            $id,
                            json_encode($e->getMessage())
                        )
                    );
                }
            }
        );
    }
}
