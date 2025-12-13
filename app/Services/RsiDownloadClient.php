<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class RsiDownloadClient
{
    private const RSI_TOKEN = 'STAR-CITIZEN.WIKI_DE_API_REQUEST';

    public static function getClient(): PendingRequest
    {

        return Http::withHeaders(
            [
                'X-RSI-Token' => self::RSI_TOKEN,
            ]
        )
            ->baseUrl(config('services.rsi_url'))
            ->timeout(60)
            ->throw();

    }
}
