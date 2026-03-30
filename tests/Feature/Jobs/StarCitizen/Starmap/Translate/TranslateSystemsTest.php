<?php

declare(strict_types=1);

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Jobs\StarCitizen\Starmap\Translate\TranslateSystems;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('translates systems without german translation', function () {
    $starsystem = Starsystem::factory()->create();
    $starsystem->setTranslation('translation', Language::ENGLISH, 'Sol');
    $starsystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Sol', 'de')
            ->andReturn('Sonne');
    });

    (new TranslateSystems)->handle(app(TranslationService::class));

    expect($starsystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBe('Sonne');
});

it('skips systems without english translation', function (): void {
    $starsystem = Starsystem::factory()->create();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateSystems)->handle(app(TranslationService::class));

    expect($starsystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('skips systems with empty english translation', function (): void {
    $starsystem = Starsystem::factory()->create();
    $starsystem->setTranslation('translation', Language::ENGLISH, '');
    $starsystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldNotReceive('translate');
    });

    (new TranslateSystems)->handle(app(TranslationService::class));

    expect($starsystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stores system translations in the configured locale', function (): void {
    config()->set('services.deepl.target_locale', 'zh_CN');
    config()->set('services.deepl.translation_locale', Language::CHINESE);

    $starsystem = Starsystem::factory()->create();
    $starsystem->setTranslation('translation', Language::ENGLISH, 'Sol');
    $starsystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('Sol', 'zh_CN')
            ->andReturn('Taiyang');
    });

    (new TranslateSystems)->handle(app(TranslationService::class));

    expect($starsystem->fresh()->getTranslation('translation', Language::CHINESE, false))->toBe('Taiyang')
        ->and($starsystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stops processing when quota is exceeded', function () {
    $firstSystem = Starsystem::factory()->create();
    $secondSystem = Starsystem::factory()->create();

    $firstSystem->setTranslation('translation', Language::ENGLISH, 'First');
    $firstSystem->save();
    $secondSystem->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondSystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de')
            ->andThrow(new QuotaExceededException('Quota exceeded'));
    });

    $job = (new TranslateSystems)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertFailedWith(QuotaExceededException::class);

    expect($firstSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stops processing when rate limit is hit', function () {
    $firstSystem = Starsystem::factory()->create();
    $secondSystem = Starsystem::factory()->create();

    $firstSystem->setTranslation('translation', Language::ENGLISH, 'First');
    $firstSystem->save();
    $secondSystem->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondSystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de')
            ->andThrow(new RateLimitException('Rate limit exceeded'));
    });

    $job = (new TranslateSystems)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertReleased(60);
    $job->assertNotFailed();

    expect($firstSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('stops processing when authentication fails', function () {
    $firstSystem = Starsystem::factory()->create();
    $secondSystem = Starsystem::factory()->create();

    $firstSystem->setTranslation('translation', Language::ENGLISH, 'First');
    $firstSystem->save();
    $secondSystem->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondSystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->once()
            ->with('First', 'de')
            ->andThrow(new AuthenticationException('Authentication failed'));
    });

    $job = (new TranslateSystems)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertFailedWith(AuthenticationException::class);

    expect($firstSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
});

it('continues to next system when translation fails', function () {
    $firstSystem = Starsystem::factory()->create();
    $secondSystem = Starsystem::factory()->create();

    $firstSystem->setTranslation('translation', Language::ENGLISH, 'First');
    $firstSystem->save();
    $secondSystem->setTranslation('translation', Language::ENGLISH, 'Second');
    $secondSystem->save();

    $this->mock(TranslationService::class, function ($mock) {
        $mock->shouldReceive('translate')
            ->twice()
            ->andReturnUsing(function (string $text) {
                if ($text === 'First') {
                    throw new TranslationException('Translation failed');
                }

                return 'Zweite';
            });
    });

    $job = (new TranslateSystems)->withFakeQueueInteractions();
    $job->handle(app(TranslationService::class));
    $job->assertNotFailed();

    expect($firstSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty()
        ->and($secondSystem->fresh()->getTranslation('translation', Language::GERMAN, false))->toBe('Zweite');
});
