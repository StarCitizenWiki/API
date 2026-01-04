<?php

use App\Http\Resources\TranslationResolver;
use App\Models\Game\Item;
use App\Models\System\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('returns locale specific translations with english fallback', function () {
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

    $request = Request::create('/translations', 'GET', ['locale' => Language::GERMAN]);

    expect(TranslationResolver::resolve($item, $request))->toBe('Hallo');

    $request = Request::create('/translations', 'GET', ['locale' => Language::CHINESE]);

    expect(TranslationResolver::resolve($item, $request))->toBe('Hello');
});

it('returns all translations keyed by locale with missing locales filled by english', function () {
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

    $request = Request::create('/translations', 'GET');

    $result = TranslationResolver::resolve($item, $request);

    expect($result)->toMatchArray([
        Language::ENGLISH => 'Hello',
        Language::GERMAN => 'Hallo',
        Language::CHINESE => 'Hello',
    ]);
});
