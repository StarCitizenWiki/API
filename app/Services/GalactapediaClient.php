<?php

declare(strict_types=1);

namespace App\Services;

final class GalactapediaClient
{
    public function __construct(private RsiDownloadClient $client) {}

    public function query(string $gql, array $variables = []): ?array
    {
        $payload = ['query' => $gql];

        if ($variables !== []) {
            $payload['variables'] = $variables;
        }

        $result = $this->client->forRsi()->post('galactapedia/graphql', $payload)->json() ?? [];

        return $result['data'] ?? null;
    }
}
