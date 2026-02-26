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
    CommLink::factory()->create(['category_id' => $category->id]);

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));
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
});

it('uses more formality for lore category', function () {
    $category = Category::factory()->create(['name' => 'Lore']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, 'A formal story');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('A formal story', 'de', 'en', 'more')
            ->andReturn('Eine formelle Geschichte');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->not->toBeNull()
        ->and($german)->toBe('Eine formelle Geschichte');
});

it('uses more formality for short stories category', function () {
    $category = Category::factory()->create(['name' => 'Short Stories']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, 'A short story');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('A short story', 'de', 'en', 'more')
            ->andReturn('Eine Kurzgeschichte');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->not->toBeNull()
        ->and($german)->toBe('Eine Kurzgeschichte');
});

it('uses less formality for other categories', function () {
    $category = Category::factory()->create(['name' => 'Development']);
    $commLink = CommLink::factory()->create(['category_id' => $category->id]);

    $commLink->setTranslation('translation', Language::ENGLISH, 'Development update');
    $commLink->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Development update', 'de', 'en', 'less')
            ->andReturn('Entwicklungsupdate');
    });

    (new TranslateCommLinks)->handle(app(TranslationService::class));

    $german = $commLink->fresh()->getTranslation('translation', Language::GERMAN, false);

    expect($german)->not->toBeNull()
        ->and($german)->toBe('Entwicklungsupdate');
});

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
