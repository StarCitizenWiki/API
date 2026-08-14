<?php

declare(strict_types=1);

use App\Http\Resources\TranslationResolver;
use App\Models\Game\Item;
use App\Models\System\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Cache;

it('returns locale specific translations with english fallback', function (): void {
    Language::query()->insert([
        ['code' => Language::ENGLISH],
        ['code' => Language::GERMAN],
        ['code' => Language::CHINESE],
    ]);

    $item = Item::factory()->make([
        'translation' => [
            Language::ENGLISH => 'Hello',
            Language::GERMAN => 'Hallo',
        ],
    ]);

    expect(TranslationResolver::resolve($item, Request::create('/translations', 'GET', ['locale' => Language::GERMAN])))->toBe('Hallo')
        ->and(TranslationResolver::resolve($item, Request::create('/translations', 'GET', ['locale' => Language::CHINESE])))->toBe('Hello');
});

it('returns all translations keyed by locale with missing locales filled by english', function (): void {
    Language::query()->insert([
        ['code' => Language::ENGLISH],
        ['code' => Language::GERMAN],
        ['code' => Language::CHINESE],
    ]);

    $item = Item::factory()->make([
        'translation' => [
            Language::ENGLISH => 'Hello',
            Language::GERMAN => 'Hallo',
        ],
    ]);

    expect(TranslationResolver::resolve($item, Request::create('/translations', 'GET')))->toMatchArray([
        Language::OLD_LANG_MAP[Language::ENGLISH] => 'Hello',
        Language::OLD_LANG_MAP[Language::GERMAN] => 'Hallo',
        Language::OLD_LANG_MAP[Language::CHINESE] => 'Hello',
    ]);
});

it('returns missing value sources unchanged', function (): void {
    $source = new MissingValue;

    expect(TranslationResolver::resolve($source, Request::create('/translations', 'GET')))->toBe($source);
});

it('returns a missing value for a null source', function (): void {
    expect(TranslationResolver::resolve(null, Request::create('/translations', 'GET')))->toBeInstanceOf(MissingValue::class);
});

it('returns null when the translation set is empty', function (): void {
    $item = Item::factory()->make([
        'translation' => [],
    ]);

    expect(TranslationResolver::resolve($item, Request::create('/translations', 'GET')))->toBeNull();
});

it('returns original locale keys when no locales are stored', function (): void {
    $item = Item::factory()->make([
        'translation' => [
            Language::ENGLISH => 'Hello',
            Language::GERMAN => 'Hallo',
        ],
    ]);

    expect(TranslationResolver::resolve($item, Request::create('/translations', 'GET')))->toBe([
        Language::ENGLISH => 'Hello',
        Language::GERMAN => 'Hallo',
    ]);
});

it('purges the locale cache when a language is saved or deleted', function (): void {
    Language::query()->insert([['code' => Language::ENGLISH]]);

    $item = Item::factory()->make([
        'translation' => [Language::ENGLISH => 'Hello'],
    ]);

    TranslationResolver::resolve($item, Request::create('/translations', 'GET'));

    expect(Cache::has(Language::LOCALES_CACHE_KEY))->toBeTrue();

    $language = Language::query()->first();
    $language->code = Language::ENGLISH;
    $language->save();

    expect(Cache::has(Language::LOCALES_CACHE_KEY))->toBeFalse();

    TranslationResolver::resolve($item, Request::create('/translations', 'GET'));

    expect(Cache::has(Language::LOCALES_CACHE_KEY))->toBeTrue();

    $language->delete();

    expect(Cache::has(Language::LOCALES_CACHE_KEY))->toBeFalse();
});

it('stores the locale cache as primitives', function (): void {
    Language::query()->insert([['code' => Language::ENGLISH]]);

    $item = Item::factory()->make([
        'translation' => [Language::ENGLISH => 'Hello'],
    ]);

    TranslationResolver::resolve($item, Request::create('/translations', 'GET'));

    expect(Cache::get(Language::LOCALES_CACHE_KEY))->toBeArray();
});
