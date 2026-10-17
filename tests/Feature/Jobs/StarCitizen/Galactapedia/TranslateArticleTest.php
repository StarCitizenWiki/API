<?php

declare(strict_types=1);

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Jobs\StarCitizen\Galactapedia\TranslateArticle;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;

function createArticleWithEnglishTranslation(string $english = 'Hello Universe'): Article
{
    $article = Article::factory()->create();
    $article->setTranslation('translation', Language::ENGLISH, $english);
    $article->save();

    return $article;
}

describe('translate article', function () {
    it('translates articles without german translation', function () {
        $article = createArticleWithEnglishTranslation();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')
                ->once()
                ->with('Hello Universe', 'de')
                ->andReturn('Hallo Universum');
        });

        (new TranslateArticle($article))->handle(app(TranslationService::class));

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBe('Hallo Universum');
    });

    it('skips articles without english translation', function () {
        $article = Article::factory()->create();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldNotReceive('translate');
        });

        (new TranslateArticle($article))->handle(app(TranslationService::class));

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
    });
});

describe('translation failures', function () {
    it('continues when a generic translation exception occurs', function () {
        $article = createArticleWithEnglishTranslation();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')
                ->once()
                ->andThrow(new TranslationException('Translation failed'));
        });

        $job = (new TranslateArticle($article))->withFakeQueueInteractions();
        $job->handle(app(TranslationService::class));
        $job->assertNotFailed();

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
    });

    it('fails the job when quota is exceeded', function () {
        $article = createArticleWithEnglishTranslation();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')
                ->once()
                ->andThrow(new QuotaExceededException('Quota exceeded'));
        });

        $job = (new TranslateArticle($article))->withFakeQueueInteractions();
        $job->handle(app(TranslationService::class));
        $job->assertFailedWith(QuotaExceededException::class);

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
    });

    it('releases the job when rate limit is hit', function () {
        $article = createArticleWithEnglishTranslation();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')
                ->once()
                ->andThrow(new RateLimitException('Rate limit exceeded'));
        });

        $job = (new TranslateArticle($article))->withFakeQueueInteractions();
        $job->handle(app(TranslationService::class));
        $job->assertReleased(60);
        $job->assertNotFailed();

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
    });

    it('fails the job when authentication fails', function () {
        $article = createArticleWithEnglishTranslation();

        $this->mock(TranslationService::class, function ($mock) {
            $mock->shouldReceive('translate')
                ->once()
                ->andThrow(new AuthenticationException('Authentication failed'));
        });

        $job = (new TranslateArticle($article))->withFakeQueueInteractions();
        $job->handle(app(TranslationService::class));
        $job->assertFailedWith(AuthenticationException::class);

        expect($article->fresh()->getTranslation('translation', Language::GERMAN, false))->toBeEmpty();
    });
});
