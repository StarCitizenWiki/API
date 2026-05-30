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

        expect($result)->toContain('<span class="mission-token">quantum drive</span>')
            ->and($result)->not->toContain('<EM4>')
            ->and($result)->not->toContain('</EM4>');
    });

    it('escapes content inside EM4 tags', function (): void {
        $result = FormatMissionText::description('Use <EM4><b>bold</b></EM4> carefully.');

        expect($result)->toContain('&lt;b&gt;bold&lt;/b&gt;')
            ->and($result)->toContain('<span class="mission-token">');
    });

    it('does not parse raw ~mission() template tokens', function (): void {
        $result = FormatMissionText::description('Go to ~mission(target_location|Crusader).');

        expect($result)->toContain('~mission(target_location|Crusader)');
    });

    it('leaves unknown brackets as escaped text', function (): void {
        $result = FormatMissionText::description('Go to [Crusader] now.');

        expect($result)->toBe('Go to [Crusader] now.');
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
        expect($result)->toContain('<span class="mission-token">real</span>');
    });

    it('handles multiple EM4 tags', function (): void {
        $result = FormatMissionText::description('<EM4>first</EM4> and <EM4>second</EM4>');

        expect($result)->toContain('<span class="mission-token">first</span>')
            ->and($result)->toContain('<span class="mission-token">second</span>');
    });

    it('handles EM4 with newlines inside', function (): void {
        $result = FormatMissionText::description("<EM4>line1\nline2</EM4>");

        expect($result)->toContain('<span class="mission-token">line1')
            ->and($result)->toContain('</span>');
    });

    it('renders single-value token brackets inline', function (): void {
        $result = FormatMissionText::description(
            'Pick up at [Pickup1|Address].',
            ['Pickup1|Address' => ['Port Tressler']],
        );

        expect($result)->toContain('<span class="mission-token">Port Tressler</span>')
            ->and($result)->not->toContain('[Pickup1|Address]');
    });

    it('escapes token values', function (): void {
        $result = FormatMissionText::description(
            'Go to [Pickup1|Address].',
            ['Pickup1|Address' => ['A < B & C']],
        );

        expect($result)->toContain('A &lt; B &amp; C');
    });

    it('renders multi-value address token labels from their base key', function (): void {
        $result = FormatMissionText::description(
            'Pick up at [Pickup1|Address].',
            ['Pickup1|Address' => ['Port Tressler', 'Orinth']],
        );

        expect($result)->toContain('title="Port Tressler / Orinth"')
            ->and($result)->toContain('>[Pickup1]</span>')
            ->and($result)->not->toContain('>[Pickup1|Address]</span>')
            ->and($result)->not->toContain('>Port Tressler</span>');
    });

    it('renders multi-value non-address token labels from their variant key', function (): void {
        $result = FormatMissionText::description(
            'Work for [Contractor|HaulCargo_AtoB].',
            ['Contractor|HaulCargo_AtoB' => ['Desc A', 'Desc B']],
        );

        expect($result)->toContain('title="Desc A / Desc B"')
            ->and($result)->toContain('>[HaulCargo_AtoB]</span>')
            ->and($result)->not->toContain('>[Contractor|HaulCargo_AtoB]</span>');
    });

    it('renders multi-value address token labels from their first segment when the last segment is address', function (): void {
        $result = FormatMissionText::description(
            'Drop at [Destination|Target|Address].',
            ['Destination|Target|Address' => ['Levski', 'Nyx - Pyro Jump Point']],
        );

        expect($result)->toContain('title="Levski / Nyx - Pyro Jump Point"')
            ->and($result)->toContain('>[Destination]</span>')
            ->and($result)->not->toContain('>[Target]</span>')
            ->and($result)->not->toContain('>[Destination|Target|Address]</span>');
    });

    it('does not render unknown brackets as placeholders', function (): void {
        $result = FormatMissionText::description(
            'Go to [Location|Address] now.',
            [],
        );

        expect($result)->toBe('Go to [Location|Address] now.');
    });

    it('renders token brackets inside EM4 spans', function (): void {
        $result = FormatMissionText::description(
            '<EM4>[Pickup1|Address]</EM4>',
            ['Pickup1|Address' => ['Port Tressler']],
        );

        expect($result)->toContain('<span class="mission-token">Port Tressler</span>');
    });
});

describe('descriptionVariants', function (): void {
    it('returns null when description is not a single bracket', function (): void {
        expect(FormatMissionText::descriptionVariants(
            'Pick up at [Pickup1|Address] and deliver.',
            ['Pickup1|Address' => ['A', 'B']],
        ))->toBeNull();
    });

    it('returns null when bracket maps to single value', function (): void {
        expect(FormatMissionText::descriptionVariants(
            '[Contractor|Desc]',
            ['Contractor|Desc' => ['Only one']],
        ))->toBeNull();
    });

    it('returns resolved variants when description is entirely a multi-value bracket', function (): void {
        $result = FormatMissionText::descriptionVariants(
            '[Contractor|HaulCargo_AtoB]',
            ['Contractor|HaulCargo_AtoB' => ['Desc A', 'Desc B']],
        );

        expect($result)->not->toBeNull()
            ->and($result)->toHaveCount(2)
            ->and($result[0])->toContain('Desc A')
            ->and($result[1])->toContain('Desc B');
    });

    it('renders EM4 and token brackets inside variants', function (): void {
        $result = FormatMissionText::descriptionVariants(
            '[Contractor|HaulCargo_AtoB]',
            [
                'Contractor|HaulCargo_AtoB' => [
                    'Go to <EM4>Port Tressler</EM4>.',
                    'Deliver to <EM4>Orinth</EM4>.',
                ],
            ],
        );

        expect($result)->not->toBeNull()
            ->and($result)->toHaveCount(2)
            ->and($result[0])->toContain('<span class="mission-token">Port Tressler</span>')
            ->and($result[1])->toContain('<span class="mission-token">Orinth</span>')
            ->and($result[0])->not->toContain('<EM4>');
    });

    it('renders token tooltips inside variants', function (): void {
        $result = FormatMissionText::descriptionVariants(
            '[Contractor|HaulCargo_AtoB]',
            [
                'Contractor|HaulCargo_AtoB' => [
                    'Pick up at [Pickup1|Address].',
                    'Deliver to [Dropoff1|Address].',
                ],
                'Pickup1|Address' => ['Area18', 'Orison'],
                'Dropoff1|Address' => ['Grim HEX', 'Everus Harbor'],
            ],
        );

        expect($result)->not->toBeNull()
            ->and($result[0])->toContain('title="Area18 / Orison"')
            ->and($result[0])->toContain('>[Pickup1]</span>')
            ->and($result[1])->toContain('title="Grim HEX / Everus Harbor"')
            ->and($result[1])->toContain('>[Dropoff1]</span>');
    });
});

describe('format', function (): void {
    it('returns null when both title and debug name are null', function (): void {
        expect(FormatMissionText::format(null, null))->toBeNull();
    });

    it('returns plain title unchanged', function (): void {
        expect(FormatMissionText::format('Eliminate Target'))->toBe('Eliminate Target');
    });

    it('does not parse raw ~mission() tokens in title', function (): void {
        expect(FormatMissionText::format('Go to ~mission(target|Crusader)'))->toBe('Go to ~mission(target|Crusader)');
    });

    it('formats debug name with difficulty suffix', function (): void {
        $result = FormatMissionText::format(null, 'aaa_bounty_hunter_hard');

        expect($result)->toContain('Hard')
            ->and($result)->toContain('Bounty Hunter');
    });

    it('does not replace placeholders in title', function (): void {
        expect(FormatMissionText::format('Delivery to [Dropoff]'))->toBe('Delivery to [Dropoff]');
    });
});
