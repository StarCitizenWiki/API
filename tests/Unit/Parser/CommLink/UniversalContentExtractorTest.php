<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

function universalExtractedText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

it('extracts supported universal elements and preserves unknown text', function (): void {
    $extractor = new UniversalContentExtractor(new Crawler(<<<'HTML'
        <div>
            <g-author author-name="Jane Smith" author-desc="Community Writer"></g-author>
            <g-faq :question-list='[{"title":"Question One","content":"<p>Answer one</p>"}]'></g-faq>
            <g-feature :is-header-declared="true">
                <template slot="title">Feature Title</template>
                <template slot="subtitle">Feature Subtitle</template>
            </g-feature>
            <g-unknown-element>Fallback text</g-unknown-element>
        </div>
        HTML));

    $text = universalExtractedText($extractor->getContent());

    expect($text)->toContain('Jane Smith')
        ->and($text)->toContain('Community Writer')
        ->and($text)->toContain('Question One')
        ->and($text)->toContain('Answer one')
        ->and($text)->toContain('Feature Title')
        ->and($text)->toContain('Feature Subtitle')
        ->and($text)->toContain('Fallback text');
});

it('returns an empty string for empty pages', function (): void {
    $extractor = new UniversalContentExtractor(new Crawler('<html><body></body></html>'));

    expect($extractor->getContent())->toBe('');
});

it('logs unknown g elements while preserving their text', function (): void {
    Log::shouldReceive('warning')
        ->once()
        ->with('No extractor for <g-unknown-element>');

    $extractor = new UniversalContentExtractor(new Crawler(<<<'HTML'
        <div>
            <g-unknown-element>Fallback text</g-unknown-element>
        </div>
        HTML));

    expect($extractor->getContent())->toBe('Fallback text');
});
