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

    $commLink->setTranslation('translation', Language::ENGLISH, 'Hello World');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Hello World', 'de', 'en', 'less')
            ->andReturn('Hallo Welt');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->not->toBeNull()
        ->and($german)->toBe('Hallo Welt');
});

it('stores comm-link translations in the configured locale', function (): void {
    config()->set('services.deepl.target_locale', 'zh_CN');
    config()->set('services.deepl.translation_locale', Language::CHINESE);

    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, 'Hello World');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Hello World', 'zh_CN', 'en', 'less')
            ->andReturn('Ni Hao');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    expect($commLink->fresh()->getTranslation('translation', Language::CHINESE, false))->toBe('Ni Hao')
        ->and($commLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('skips comm-links with existing german translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, 'Hello World');
    $commLink->setTranslation('translation', Language::GERMAN, 'Existing Translation');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->toBe('Existing Translation');
});

it('skips comm-links without english translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    expect($commLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('skips comm-links with empty english translation', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, '');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    expect($commLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('uses category-based formality rules', function (
    string $categoryName,
    string $englishTranslation,
    string $expectedFormality,
    string $expectedGermanTranslation
) {
    $category = Category::factory()->create(['name' => $categoryName]);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, $englishTranslation);
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) use ($englishTranslation, $expectedFormality, $expectedGermanTranslation) {
        $mock->shouldReceive('translate')
            ->once()
            ->with($englishTranslation, 'de', 'en', $expectedFormality)
            ->andReturn($expectedGermanTranslation);
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->not->toBeNull()
        ->and($german)->toBe($expectedGermanTranslation);
})->with([
    'lore uses more formality' => ['Lore', 'A formal story', 'more', 'Eine formelle Geschichte'],
    'short stories uses more formality' => ['Short Stories', 'A short story', 'more', 'Eine Kurzgeschichte'],
    'other categories use less formality' => ['Development', 'Development update', 'less', 'Entwicklungsupdate'],
]);

it('stops processing when quota is exceeded', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $firstCommLink = CommLink::factory()->create(['category_id' => $category->id]);
    $secondCommLink = CommLink::factory()->create(['category_id' => $category->id]);

    $firstCommLink->setTranslation('translation', Language::ENGLISH, 'First');
    $firstCommLink->save();

    $secondCommLink->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondCommLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de', 'en', 'less')
            ->andThrow(new QuotaExceededException('Quota exceeded'));
    });

    $job = (new TranslateCommLinks)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertFailedWith(QuotaExceededException::class);

    expect($firstCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stops processing when rate limit is hit', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $firstCommLink = CommLink::factory()->create(['category_id' => $category->id]);
    $secondCommLink = CommLink::factory()->create(['category_id' => $category->id]);

    $firstCommLink->setTranslation('translation', Language::ENGLISH, 'First');
    $firstCommLink->save();

    $secondCommLink->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondCommLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de', 'en', 'less')
            ->andThrow(new RateLimitException('Rate limit exceeded'));
    });

    $job = (new TranslateCommLinks)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertReleased(60);
    $job->assertNotFailed();

    expect($firstCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stops processing when authentication fails', function () {
    $category = Category::factory()->create(['name' => 'General']);
    $firstCommLink = CommLink::factory()->create(['category_id' => $category->id]);
    $secondCommLink = CommLink::factory()->create(['category_id' => $category->id]);

    $firstCommLink->setTranslation('translation', Language::ENGLISH, 'First');
    $firstCommLink->save();

    $secondCommLink->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondCommLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de', 'en', 'less')
            ->andThrow(new AuthenticationException('Authentication failed'));
    });

    $job = (new TranslateCommLinks)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertFailedWith(AuthenticationException::class);

    expect($firstCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('continues to next comm-link when translation fails', function () {
    $category = Category::factory()->create(['name' => fake()->word()]);
    $commLink1 = CommLink::factory()->create(['category_id' => $category->id]);
    $commLink2 = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink1->setTranslation('translation', Language::ENGLISH, 'First');
    $commLink1->save();

    $commLink2->setTranslation('translation', Language::ENGLISH, 'Second');
    $commLink2->save();

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

    $job = (new TranslateCommLinks)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertNotFailed();

    expect($commLink1->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($commLink2->fresh()->getTranslation('translation', Language::GERMAN, false))->toBe('Zweite');
});

it('can limit translation to specific comm-link ids', function (): void {
    $category = Category::factory()->create(['name' => 'General']);
    $firstCommLink = CommLink::factory()->create(['category_id' => $category->id]);
    $secondCommLink = CommLink::factory()->create(['category_id' => $category->id]);

    $firstCommLink->setTranslation('translation', Language::ENGLISH, 'First');
    $firstCommLink->save();

    $secondCommLink->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondCommLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de', 'en', 'less')
            ->andReturn('Erste');
    });

    (new TranslateCommLinks([$firstCommLink->cig_id]))->handle(app(TranslationService::class));

    expect($firstCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBe('Erste')
        ->and($secondCommLink->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});
