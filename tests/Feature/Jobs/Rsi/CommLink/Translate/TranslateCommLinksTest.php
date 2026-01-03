<?php

declare(strict_types=1);

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Models\Rsi\CommLink\Category;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('translates comm-links without german translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Hello World',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Hello World', 'de', 'en', 'less')
            ->andReturn('Hallo Welt');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->translations()
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german)->not->toBeNull()
        ->and($german->translation)->toBe('Hallo Welt')
        ->and($german->proofread)->toBeFalse();
});

it('skips comm-links with existing german translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Hello World',
        'proofread' => false,
    ]);

    $commLink->translations()->create([
        'locale_code' => Language::GERMAN,
        'translation' => 'Existing Translation',
        'proofread' => true,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->translations()
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german->translation)->toBe('Existing Translation')
        ->and($german->proofread)->toBeTrue();
});

it('skips comm-links without english translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    CommLink::factory()->create(['category_id' => $category->id]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));
});

it('skips comm-links with empty english translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => '',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));
});

it('uses more formality for lore category', function () {
    $category = Category::factory()->create(['name' => 'Lore']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'A formal story',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('A formal story', 'de', 'en', 'more')
            ->andReturn('Eine formelle Geschichte');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->translations()
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german)->not->toBeNull()
        ->and($german->translation)->toBe('Eine formelle Geschichte');
});

it('uses more formality for short stories category', function () {
    $category = Category::factory()->create(['name' => 'Short Stories']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'A short story',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('A short story', 'de', 'en', 'more')
            ->andReturn('Eine Kurzgeschichte');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->translations()
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german)->not->toBeNull()
        ->and($german->translation)->toBe('Eine Kurzgeschichte');
});

it('uses less formality for other categories', function () {
    $category = Category::factory()->create(['name' => 'Development']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Development update',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Development update', 'de', 'en', 'less')
            ->andReturn('Entwicklungsupdate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->translations()
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german)->not->toBeNull()
        ->and($german->translation)->toBe('Entwicklungsupdate');
});

it('stops processing when quota is exceeded', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Test',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->andThrow(new QuotaExceededException('Quota exceeded'));
    });

    try {
        (new TranslateCommLinks)->handle(app(TranslationService::class));
    } catch (QuotaExceededException $e) {
        // Exception is expected
    }

    expect($commLink->fresh()->translations()->where('locale_code', Language::GERMAN)->exists())->toBeFalse();
});

it('stops processing when rate limit is hit', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Test',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->andThrow(new RateLimitException('Rate limit exceeded'));
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    expect($commLink->fresh()->translations()->where('locale_code', Language::GERMAN)->exists())->toBeFalse();
});

it('stops processing when authentication fails', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Test',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->andThrow(new AuthenticationException('Authentication failed'));
    });

    try {
        (new TranslateCommLinks)->handle(app(TranslationService::class));
    } catch (AuthenticationException $e) {
        // Exception is expected
    }

    expect($commLink->fresh()->translations()->where('locale_code', Language::GERMAN)->exists())->toBeFalse();
});

it('continues to next comm-link when translation fails', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink1 = CommLink::factory()->create(['category_id' => $category->id]);
    $commLink2 = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink1->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'First',
        'proofread' => false,
    ]);

    $commLink2->translations()->create([
        'locale_code' => Language::ENGLISH,
        'translation' => 'Second',
        'proofread' => false,
    ]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->twice()
            ->andReturnUsing(function ($text) {
                if ($text === 'First') {
                    throw new TranslationException('Translation failed');
                }

                return 'Zweite';
            });
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    expect($commLink1->translations()->where('locale_code', Language::GERMAN)->exists())->toBeFalse()
        ->and($commLink2->translations()->where('locale_code', Language::GERMAN)->exists())->toBeTrue();
});
