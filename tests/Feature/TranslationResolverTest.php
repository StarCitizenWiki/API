<?php

use App\Http\Resources\TranslationResolver;
use App\Models\Game\ItemTranslation;
use App\Models\System\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('returns locale specific translations with english fallback', function () {
    Language::query()->insert([
        ['locale_code' => Language::ENGLISH],
        ['locale_code' => Language::GERMAN],
        ['locale_code' => Language::CHINESE],
    ]);

    $translations = collect([
        ItemTranslation::make([
            'locale_code' => Language::ENGLISH,
            'translation' => 'Hello',
        ]),
        ItemTranslation::make([
            'locale_code' => Language::GERMAN,
            'translation' => 'Hallo',
        ]),
    ]);

    $request = Request::create('/translations', 'GET', ['locale' => Language::GERMAN]);

    expect(TranslationResolver::resolve($translations, $request))->toBe('Hallo');

    $request = Request::create('/translations', 'GET', ['locale' => Language::CHINESE]);

    expect(TranslationResolver::resolve($translations, $request))->toBe('Hello');
});

it('returns all translations keyed by locale with missing locales filled by english', function () {
    Language::query()->insert([
        ['locale_code' => Language::ENGLISH],
        ['locale_code' => Language::GERMAN],
        ['locale_code' => Language::CHINESE],
    ]);

    $translations = collect([
        ItemTranslation::make([
            'locale_code' => Language::ENGLISH,
            'translation' => 'Hello',
        ]),
        ItemTranslation::make([
            'locale_code' => Language::GERMAN,
            'translation' => 'Hallo',
        ]),
    ]);

    $request = Request::create('/translations', 'GET');

    $result = TranslationResolver::resolve($translations, $request);

    expect($result)->toMatchArray([
        Language::ENGLISH => 'Hello',
        Language::GERMAN => 'Hallo',
        Language::CHINESE => 'Hello',
    ]);
});
