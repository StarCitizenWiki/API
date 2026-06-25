<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\System\Language;
use App\Models\User;

$resolveTranslationModel = static function (string $type): array {
    return match ($type) {
        'comm-link' => [
            CommLink::factory()->create([
                'title' => 'Comm-Link Example',
                'translation' => [
                    Language::ENGLISH => 'Comm-Link Example',
                    Language::GERMAN => 'Comm-Link Beispiel',
                    Language::CHINESE => 'Comm-Link 示例',
                    Language::FRENCH => 'Exemple Comm-Link',
                ],
            ]),
            'Comm-Link Example',
            [
                Language::ENGLISH => 'Comm-Link Example',
                Language::GERMAN => 'Comm-Link Beispiel',
                Language::CHINESE => 'Comm-Link 示例',
                Language::FRENCH => 'Exemple Comm-Link',
            ],
        ],
        'article' => [
            Article::factory()->create([
                'title' => 'Galactapedia Example',
                'translation' => [
                    Language::ENGLISH => 'Galactapedia Example',
                    Language::GERMAN => 'Galactapedia Beispiel',
                    Language::CHINESE => 'Galactapedia 示例',
                    Language::FRENCH => 'Exemple Galactapedia',
                ],
            ]),
            'Galactapedia Example',
            [
                Language::ENGLISH => 'Galactapedia Example',
                Language::GERMAN => 'Galactapedia Beispiel',
                Language::CHINESE => 'Galactapedia 示例',
                Language::FRENCH => 'Exemple Galactapedia',
            ],
        ],
        'smSize' => [
            Size::factory()->create([
                'slug' => 'small',
                'translation' => [
                    Language::ENGLISH => 'Small',
                    Language::GERMAN => 'Klein',
                    Language::CHINESE => '小',
                    Language::FRENCH => 'Petit',
                ],
            ]),
            null,
            [
                Language::ENGLISH => 'Small',
                Language::GERMAN => 'Klein',
                Language::CHINESE => '小',
                Language::FRENCH => 'Petit',
            ],
        ],
        'smFocus' => [
            Focus::factory()->create([
                'slug' => 'combat',
                'translation' => [
                    Language::ENGLISH => 'Combat',
                    Language::GERMAN => 'Kampf',
                    Language::CHINESE => '战斗',
                    Language::FRENCH => 'Combat',
                ],
            ]),
            null,
            [
                Language::ENGLISH => 'Combat',
                Language::GERMAN => 'Kampf',
                Language::CHINESE => '战斗',
                Language::FRENCH => 'Combat',
            ],
        ],
        'smType' => [
            Type::factory()->create([
                'slug' => 'fighter',
                'translation' => [
                    Language::ENGLISH => 'Fighter',
                    Language::GERMAN => 'Jäger',
                    Language::CHINESE => '战斗机',
                    Language::FRENCH => 'Chasseur',
                ],
            ]),
            null,
            [
                Language::ENGLISH => 'Fighter',
                Language::GERMAN => 'Jäger',
                Language::CHINESE => '战斗机',
                Language::FRENCH => 'Chasseur',
            ],
        ],
        default => throw new InvalidArgumentException("Unsupported translation type [{$type}]."),
    };
};

it('renders the translations index with visible rows and edit links', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $commLink = CommLink::factory()->create([
        'title' => 'Comm-Link Example',
        'translation' => [
            Language::ENGLISH => 'Comm-Link Example',
        ],
    ]);
    $article = Article::factory()->create([
        'title' => 'Galactapedia Example',
        'translation' => [
            Language::ENGLISH => 'Galactapedia Example',
        ],
    ]);
    $size = Size::factory()->create([
        'slug' => 'small',
        'translation' => [
            Language::ENGLISH => 'Small',
        ],
    ]);
    $focus = Focus::factory()->create([
        'slug' => 'combat',
        'translation' => [
            Language::ENGLISH => 'Combat',
        ],
    ]);
    $type = Type::factory()->create([
        'slug' => 'fighter',
        'translation' => [
            Language::ENGLISH => 'Fighter',
        ],
    ]);

    $response = $this->actingAs($admin)->get(route('admin.translations.index'));

    $response->assertSuccessful()
        ->assertViewIs('admin.translations.index')
        ->assertViewHas('commLinks', function ($commLinks) use ($commLink): bool {
            return collect($commLinks->items())->pluck('id')->all() === [$commLink->id];
        })
        ->assertViewHas('articles', function ($articles) use ($article): bool {
            return collect($articles->items())->pluck('id')->all() === [$article->id];
        })
        ->assertViewHas('smSizes', function ($smSizes) use ($size): bool {
            return $smSizes->pluck('id')->all() === [$size->id];
        })
        ->assertViewHas('smFocuses', function ($smFocuses) use ($focus): bool {
            return $smFocuses->pluck('id')->all() === [$focus->id];
        })
        ->assertViewHas('smTypes', function ($smTypes) use ($type): bool {
            return $smTypes->pluck('id')->all() === [$type->id];
        })
        ->assertSeeTextInOrder([
            'Translations Management',
            'Comm-Links',
            'Comm-Link Example',
            'Articles',
            'Galactapedia Example',
            'Ship-Matrix',
            'small',
            'combat',
            'fighter',
        ]);
});

it('renders the edit page for each translation resolver branch', function (string $type) use ($resolveTranslationModel): void {
    $admin = User::factory()->create(['is_admin' => true]);
    [$model, $expectedHeading, $translations] = $resolveTranslationModel($type);

    $response = $this->actingAs($admin)->get(route('admin.translations.edit', [
        'type' => $type,
        'id' => $model->id,
    ]));

    $response->assertSuccessful()
        ->assertViewIs('admin.translations.edit')
        ->assertViewHas('model', fn ($viewModel): bool => $viewModel->is($model))
        ->assertViewHas('type', fn (string $viewType): bool => $viewType === $type)
        ->assertViewHas('translations', fn (array $viewTranslations): bool => $viewTranslations === $translations)
        ->assertSeeText('Edit Translations')
        ->assertSeeText('ID: '.$model->id)
        ->assertSeeText($translations[Language::ENGLISH])
        ->assertSeeText($translations[Language::GERMAN])
        ->assertSeeText($translations[Language::CHINESE])
        ->assertSeeText($translations[Language::FRENCH]);

    if ($expectedHeading !== null) {
        $response->assertSeeText($expectedHeading);
    }
})->with([
    'comm-link',
    'article',
    'smSize',
    'smFocus',
    'smType',
]);

it('updates translations for each resolver branch', function (string $type) use ($resolveTranslationModel): void {
    $admin = User::factory()->create(['is_admin' => true]);
    [$model, , $translations] = $resolveTranslationModel($type);

    $updatedTranslations = [
        Language::ENGLISH => $translations[Language::ENGLISH].' Updated',
        Language::GERMAN => $translations[Language::GERMAN].' Aktualisiert',
        Language::CHINESE => $translations[Language::CHINESE].' 更新',
        Language::FRENCH => $translations[Language::FRENCH].' Mis à jour',
    ];

    $response = $this->actingAs($admin)->put(route('admin.translations.update', [
        'type' => $type,
        'id' => $model->id,
    ]), [
        'translations' => $updatedTranslations,
    ]);

    $response->assertRedirect(route('admin.translations.index'))
        ->assertSessionHas('success', 'Translations updated successfully.');

    $model->refresh();

    expect($model->getTranslations('translation'))->toBe($updatedTranslations);
})->with([
    'comm-link',
    'article',
    'smSize',
    'smFocus',
    'smType',
]);

it('filters blank translations when updating', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $commLink = CommLink::factory()->create([
        'translation' => [
            Language::ENGLISH => 'Initial English translation',
            Language::GERMAN => 'Initial German translation',
            Language::CHINESE => 'Initial Chinese translation',
            Language::FRENCH => 'Initial French translation',
        ],
    ]);

    $response = $this->actingAs($admin)->put(route('admin.translations.update', [
        'type' => 'comm-link',
        'id' => $commLink->id,
    ]), [
        'translations' => [
            Language::ENGLISH => 'Updated English translation',
            Language::GERMAN => '',
            Language::CHINESE => null,
            Language::FRENCH => '',
        ],
    ]);

    $response->assertRedirect(route('admin.translations.index'))
        ->assertSessionHas('success', 'Translations updated successfully.');

    $commLink->refresh();

    expect($commLink->getTranslations('translation'))->toBe([
        Language::ENGLISH => 'Updated English translation',
        Language::GERMAN => 'Initial German translation',
        Language::CHINESE => 'Initial Chinese translation',
        Language::FRENCH => 'Initial French translation',
    ]);
});

it('rejects missing translations when updating', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $commLink = CommLink::factory()->create([
        'translation' => [
            Language::ENGLISH => 'Initial English translation',
        ],
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.translations.edit', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]))
        ->put(route('admin.translations.update', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]), []);

    $response->assertRedirect(route('admin.translations.edit', [
        'type' => 'comm-link',
        'id' => $commLink->id,
    ]));
    $response->assertSessionHasErrors([
        'translations' => 'Translations data is required.',
    ]);

    $commLink->refresh();

    expect($commLink->getTranslations('translation'))->toBe([
        Language::ENGLISH => 'Initial English translation',
    ]);
});

it('rejects non-array translations when updating', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $commLink = CommLink::factory()->create([
        'translation' => [
            Language::ENGLISH => 'Initial English translation',
        ],
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin.translations.edit', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]))
        ->put(route('admin.translations.update', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]), [
            'translations' => 'not-an-array',
        ]);

    $response->assertRedirect(route('admin.translations.edit', [
        'type' => 'comm-link',
        'id' => $commLink->id,
    ]));
    $response->assertSessionHasErrors([
        'translations' => 'Translations must be an array.',
    ]);

    $commLink->refresh();

    expect($commLink->getTranslations('translation'))->toBe([
        Language::ENGLISH => 'Initial English translation',
    ]);
});
