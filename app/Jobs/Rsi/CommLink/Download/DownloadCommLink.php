<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Download;

use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadCommLink implements ShouldQueue
{
    use Queueable;

    private const COMM_LINK_PATH = '/comm-link/SCW/%d-IMPORT';

    private const TOKEN_PATTERN = "/'token'\\s?\\:\\s?'[A-Za-z0-9\\+\\:\\-_\\/]+'/";

    private const CONTENT_MARKERS = [
        'id="post"',
        'id="subscribers"',
        'id="layout-system"',
    ];

    public int $timeout = 120;

    public function __construct(
        public readonly int $commLinkId,
        public readonly bool $skipExisting = true,
    ) {}

    public function handle(RsiDownloadClient $client): void
    {
        if ($this->skipExisting && Storage::disk('comm_links')->exists((string) $this->commLinkId)) {
            Log::debug('Skipping existing Comm-Link download.', ['id' => $this->commLinkId]);

            return;
        }

        try {
            $response = $client->base()->get($this->buildUrl());
        } catch (ConnectionException $e) {
            Log::info('Comm-Link download skipped due to connection error.', [
                'id' => $this->commLinkId,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if ($response->serverError()) {
            Log::warning('Comm-Link download failed with server error.', [
                'id' => $this->commLinkId,
                'status' => $response->status(),
            ]);

            $this->release(300);

            return;
        }

        if ($response->clientError()) {
            Log::info('Comm-Link download failed with client error.', [
                'id' => $this->commLinkId,
                'status' => $response->status(),
            ]);

            return;
        }

        $content = $this->sanitizeContent($response->body());

        if (! Str::contains($content, self::CONTENT_MARKERS)) {
            Log::info('Comm-Link download skipped due to missing content markers.', [
                'id' => $this->commLinkId,
            ]);

            return;
        }

        Storage::disk('comm_links')->put($this->filePath(), $content);
        $this->pruneStoredFiles();
    }

    private function buildUrl(): string
    {
        return rtrim((string) config('services.rsi_url'), '/').sprintf(self::COMM_LINK_PATH, $this->commLinkId);
    }

    private function sanitizeContent(string $content): string
    {
        return preg_replace(self::TOKEN_PATTERN, "'token' : ''", $content) ?? $content;
    }

    private function filePath(): string
    {
        $filename = Carbon::now()->format('Y-m-d_His');

        return sprintf('%d/%s.html', $this->commLinkId, $filename);
    }

    private function pruneStoredFiles(): void
    {
        $files = Storage::disk('comm_links')->files((string) $this->commLinkId);

        if (count($files) <= 2) {
            return;
        }

        sort($files);

        $filesToDelete = array_slice($files, 1, -1);

        Storage::disk('comm_links')->delete($filesToDelete);
    }
}
