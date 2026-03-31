@props([
    'canonical' => null,
    'keywords' => [],
    'robots' => null,
    'ogType' => 'website',
    'ogTitle' => null,
    'ogDescription' => null,
    'ogUrl' => null,
    'ogImage' => null,
    'twitterCard' => 'summary',
    'twitterTitle' => null,
    'twitterDescription' => null,
    'twitterImage' => null,
    'structuredData' => [],
])

@php
    $normalizeString = static function (mixed $value): ?string {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    };

    if (is_string($keywords)) {
        $keywordsList = array_values(array_filter(
            array_map(
                static fn (string $keyword): string => trim($keyword),
                explode(',', $keywords),
            ),
            static fn (string $keyword): bool => $keyword !== '',
        ));
    } elseif (is_array($keywords)) {
        $keywordsList = array_values(array_filter(
            array_map(static fn (mixed $keyword): ?string => $normalizeString($keyword), $keywords),
            static fn (?string $keyword): bool => $keyword !== null,
        ));
    } else {
        $keywordsList = [];
    }

    $canonical = $normalizeString($canonical);
    $robots = $normalizeString($robots);
    $ogType = $normalizeString($ogType) ?? 'website';
    $ogTitle = $normalizeString($ogTitle);
    $ogDescription = $normalizeString($ogDescription);
    $ogUrl = $normalizeString($ogUrl) ?? $canonical;
    $ogImage = $normalizeString($ogImage);
    $twitterCard = $normalizeString($twitterCard) ?? 'summary';
    $twitterTitle = $normalizeString($twitterTitle);
    $twitterDescription = $normalizeString($twitterDescription);
    $twitterImage = $normalizeString($twitterImage) ?? $ogImage;

    $structuredDataBlocks = [];

    if (is_array($structuredData) && $structuredData !== []) {
        $candidateBlocks = array_is_list($structuredData)
            ? $structuredData
            : [$structuredData];

        foreach ($candidateBlocks as $candidateBlock) {
            if (is_array($candidateBlock) && $candidateBlock !== []) {
                $structuredDataBlocks[] = $candidateBlock;
            }
        }
    }
@endphp

@if ($canonical)
    <link rel="canonical" href="{{ $canonical }}">
@endif

@if ($robots)
    <meta name="robots" content="{{ $robots }}">
@endif

@if ($keywordsList !== [])
    <meta name="keywords" content="{{ implode(',', $keywordsList) }}">
@endif

<meta property="og:type" content="{{ $ogType }}">

@if ($ogTitle)
    <meta property="og:title" content="{{ $ogTitle }}">
@endif

@if ($ogUrl)
    <meta property="og:url" content="{{ $ogUrl }}">
@endif

@if ($ogDescription)
    <meta property="og:description" content="{{ $ogDescription }}">
@endif

@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $twitterCard }}">

@if ($twitterTitle)
    <meta name="twitter:title" content="{{ $twitterTitle }}">
@endif

@if ($twitterDescription)
    <meta name="twitter:description" content="{{ $twitterDescription }}">
@endif

@if ($twitterImage)
    <meta name="twitter:image" content="{{ $twitterImage }}">
@endif

@foreach ($structuredDataBlocks as $structuredDataBlock)
    <script type="application/ld+json">{!! json_encode($structuredDataBlock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endforeach
