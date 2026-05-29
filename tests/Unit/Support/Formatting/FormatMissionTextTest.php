<?php

declare(strict_types=1);

use App\Support\Formatting\FormatMissionText;

describe('description', function (): void {
    it('returns null for null input', function (): void {
        expect(FormatMissionText::description(null))->toBeNull();
    });

    it('returns empty string for empty input', function (): void {
        expect(FormatMissionText::description(''))->toBe('');
    });

    it('escapes raw HTML in description text', function (): void {
        $result = FormatMissionText::description('<script>alert("xss")</script>');

        expect($result)->not->toContain('<script>')
            ->and($result)->toContain('&lt;script&gt;');
    });

    it('converts newlines to br tags', function (): void {
        $result = FormatMissionText::description("Line one\nLine two");

        expect($result)->toContain('<br />');
    });

    it('collapses duplicate newlines', function (): void {
        $result = FormatMissionText::description("Line one\n\n\nLine two");

        // After collapsing, there should be only one newline -> one <br>
        expect(substr_count($result, '<br'))->toBe(1);
    });

    it('replaces EM4 tags with styled span', function (): void {
        $result = FormatMissionText::description('Use <EM4>quantum drive</EM4> to travel.');

        expect($result)->toContain('<span class="text-secondary font-mono">quantum drive</span>')
            ->and($result)->not->toContain('<EM4>')
            ->and($result)->not->toContain('</EM4>');
    });

    it('escapes content inside EM4 tags', function (): void {
        $result = FormatMissionText::description('Use <EM4><b>bold</b></EM4> carefully.');

        expect($result)->toContain('&lt;b&gt;bold&lt;/b&gt;')
            ->and($result)->toContain('<span class="text-secondary font-mono">');
    });

    it('replaces ~mission() template tokens', function (): void {
        $result = FormatMissionText::description('Go to ~mission(target_location|Crusader).');

        expect($result)->toContain('[Crusader]')
            ->and($result)->not->toContain('~mission(');
    });

    it('replaces ~mission() tokens without pipe as headline', function (): void {
        $result = FormatMissionText::description('Complete ~mission(destroy_target).');

        expect($result)->toContain('[Destroy Target]')
            ->and($result)->not->toContain('~mission(');
    });

    it('wraps bracket content in styled span', function (): void {
        $result = FormatMissionText::description('Go to [Crusader] now.');

        expect($result)->toContain('<span class="text-secondary font-mono">[Crusader]</span>');
    });

    it('does not inject spans from literal span tags in raw input', function (): void {
        $malicious = '<span class="text-secondary font-mono">injected</span>';
        $result = FormatMissionText::description($malicious);

        // The raw span should be escaped, not preserved
        expect($result)->toContain('&lt;span')
            ->and($result)->not->toContain('<span class="text-secondary font-mono">injected</span>');
    });

    it('preserves EM4 spans even when raw input contains fake span tags', function (): void {
        $input = '<span class="text-secondary font-mono">fake</span> and <EM4>real</EM4>';

        $result = FormatMissionText::description($input);

        // The fake span should be escaped
        expect($result)->toContain('&lt;span class=&quot;text-secondary font-mono&quot;&gt;fake&lt;/span&gt;');
        // The real EM4 span should be preserved
        expect($result)->toContain('<span class="text-secondary font-mono">real</span>');
    });

    it('handles multiple EM4 tags', function (): void {
        $result = FormatMissionText::description('<EM4>first</EM4> and <EM4>second</EM4>');

        expect($result)->toContain('<span class="text-secondary font-mono">first</span>')
            ->and($result)->toContain('<span class="text-secondary font-mono">second</span>');
    });

    it('handles EM4 with newlines inside', function (): void {
        $result = FormatMissionText::description("<EM4>line1\nline2</EM4>");

        expect($result)->toContain('<span class="text-secondary font-mono">line1')
            ->and($result)->toContain('</span>');
    });
});

describe('format', function (): void {
    it('returns null when both title and debug name are null', function (): void {
        expect(FormatMissionText::format(null, null))->toBeNull();
    });

    it('returns plain title unchanged', function (): void {
        expect(FormatMissionText::format('Eliminate Target'))->toBe('Eliminate Target');
    });

    it('replaces ~mission() tokens in title when result is readable', function (): void {
        expect(FormatMissionText::format('Go to ~mission(target|Crusader)'))->toContain('Crusader');
    });

    it('formats entirely-template title from extracted key', function (): void {
        $result = FormatMissionText::format('~mission(target)', 'aaa_bounty_hunter_easy');

        expect($result)->toBe('Target');
    });

    it('formats debug name with difficulty suffix', function (): void {
        $result = FormatMissionText::format(null, 'aaa_bounty_hunter_hard');

        expect($result)->toContain('Hard')
            ->and($result)->toContain('Bounty Hunter');
    });
});
