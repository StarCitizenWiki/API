<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\System\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

it('redirects guests to login on translations index', function (): void {
    $response = $this->get(route('admin.translations.index'));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users on translations index', function (): void {
    $nonAdmin = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($nonAdmin)
        ->get(route('admin.translations.index'));

    $response->assertForbidden();
});

it('allows admins to view translations index with expected view data', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $commLink = CommLink::factory()->create([
        'translation' => [Language::ENGLISH => 'CommLink EN'],
    ]);
    $article = Article::factory()->create([
        'translation' => [Language::ENGLISH => 'Article EN'],
    ]);
    $size = Size::factory()->create([
        'translation' => [Language::ENGLISH => 'Size EN'],
    ]);
    $focus = Focus::factory()->create([
        'translation' => [Language::ENGLISH => 'Focus EN'],
    ]);
    $type = Type::factory()->create([
        'translation' => [Language::ENGLISH => 'Type EN'],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.translations.index'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.translations.index');
    $response->assertViewHas('commLinks', function (LengthAwarePaginator $commLinks) use ($commLink): bool {
        return $commLinks->getCollection()->pluck('id')->contains($commLink->id);
    });
    $response->assertViewHas('articles', function (LengthAwarePaginator $articles) use ($article): bool {
        return $articles->getCollection()->pluck('id')->contains($article->id);
    });
    $response->assertViewHas('smSizes', function (EloquentCollection $smSizes) use ($size): bool {
        return $smSizes->pluck('id')->contains($size->id);
    });
    $response->assertViewHas('smFocuses', function (EloquentCollection $smFocuses) use ($focus): bool {
        return $smFocuses->pluck('id')->contains($focus->id);
    });
    $response->assertViewHas('smTypes', function (EloquentCollection $smTypes) use ($type): bool {
        return $smTypes->pluck('id')->contains($type->id);
    });
});

it('redirects guests to login on translations edit', function (): void {
    $commLink = CommLink::factory()->create();

    $response = $this->get(route('admin.translations.edit', [
        'type' => 'comm-link',
        'id' => $commLink->id,
    ]));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users on translations edit', function (): void {
    $nonAdmin = User::factory()->create(['is_admin' => false]);
    $commLink = CommLink::factory()->create();

    $response = $this->actingAs($nonAdmin)
        ->get(route('admin.translations.edit', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]));

    $response->assertForbidden();
});

it('allows admins to view edit page with type and existing translations', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $expectedTranslations = [
        Language::ENGLISH => 'Existing English translation',
        Language::GERMAN => 'Existing German translation',
        Language::CHINESE => 'Existing Chinese translation',
    ];

    $commLink = CommLink::factory()->create([
        'translation' => $expectedTranslations,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.translations.edit', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]));

    $response->assertSuccessful();
    $response->assertViewIs('admin.translations.edit');
    $response->assertViewHas('type', 'comm-link');
    $response->assertViewHas('model', function (CommLink $model) use ($commLink): bool {
        return $model->is($commLink);
    });
    $response->assertViewHas('translations', function (array $translations) use ($expectedTranslations): bool {
        return $translations === $expectedTranslations;
    });
});

it('redirects guests to login on translations update', function (): void {
    $commLink = CommLink::factory()->create([
        'translation' => [Language::ENGLISH => 'Initial English translation'],
    ]);

    $response = $this->put(route('admin.translations.update', [
        'type' => 'comm-link',
        'id' => $commLink->id,
    ]), [
        'translations' => [
            Language::ENGLISH => 'Updated English translation',
            Language::GERMAN => 'Updated German translation',
            Language::CHINESE => 'Updated Chinese translation',
        ],
    ]);

    $response->assertRedirect(route('login'));

    $commLink->refresh();
    expect($commLink->getTranslations('translation'))->toBe([
        Language::ENGLISH => 'Initial English translation',
    ]);
});

it('forbids non-admin users on translations update', function (): void {
    $nonAdmin = User::factory()->create(['is_admin' => false]);
    $commLink = CommLink::factory()->create([
        'translation' => [Language::ENGLISH => 'Initial English translation'],
    ]);

    $response = $this->actingAs($nonAdmin)
        ->put(route('admin.translations.update', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]), [
            'translations' => [
                Language::ENGLISH => 'Updated English translation',
                Language::GERMAN => 'Updated German translation',
                Language::CHINESE => 'Updated Chinese translation',
            ],
        ]);

    $response->assertForbidden();

    $commLink->refresh();
    expect($commLink->getTranslations('translation'))->toBe([
        Language::ENGLISH => 'Initial English translation',
    ]);
});

it('updates translations and redirects admins to index with success flash', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $commLink = CommLink::factory()->create([
        'translation' => [
            Language::ENGLISH => 'Initial English translation',
            Language::GERMAN => 'Initial German translation',
        ],
    ]);
    $updatedTranslations = [
        Language::ENGLISH => 'Updated English translation',
        Language::GERMAN => 'Updated German translation',
        Language::CHINESE => 'Updated Chinese translation',
    ];

    $response = $this->actingAs($admin)
        ->put(route('admin.translations.update', [
            'type' => 'comm-link',
            'id' => $commLink->id,
        ]), [
            'translations' => $updatedTranslations,
        ]);

    $response->assertRedirect(route('admin.translations.index'));
    $response->assertSessionHas('success', 'Translations updated successfully.');

    $commLink->refresh();
    expect($commLink->getTranslations('translation'))->toBe($updatedTranslations);
});
