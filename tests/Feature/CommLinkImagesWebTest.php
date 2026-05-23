<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Rsi\CommLinkController;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash;
use App\Models\User;
use App\Services\ImageHash\PdqHasher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;

describe('index', function (): void {
    it('lists comm-link images ordered by newest first', function (): void {
        $older = Image::factory()->create([
            'alt' => 'Older image',
        ]);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();

        $newer = Image::factory()->create([
            'alt' => 'Newer image',
        ]);
        $newer->forceFill(['created_at' => now()->subDay()])->save();

        $commLink = CommLink::factory()->create([
            'title' => 'Inside Star Citizen',
        ]);
        $newer->commLinks()->attach($commLink->id);

        $response = $this->get(route('web.comm-links.images.index'));

        $response->assertOk()
            ->assertViewIs('comm-links.images.index')
            ->assertSeeInOrder([
                'Newer image',
                'Older image',
            ])
            ->assertSee('Inside Star Citizen');
    });
});

describe('show', function (): void {
    it('renders the comm-link image show view with api data', function (): void {
        $commLink = CommLink::factory()->create([
            'cig_id' => 14001,
            'title' => 'Inside Star Citizen',
        ]);

        $image = Image::factory()->create([
            'alt' => 'Comm-Link Hero',
        ]);

        $commLink->images()->attach($image->id);

        $response = $this->get(route('web.comm-links.images.show', $image->id));

        $response->assertOk()
            ->assertViewIs('comm-links.images.show')
            ->assertSee('Comm-Link Hero')
            ->assertSee('Inside Star Citizen')
            ->assertSee((string) $commLink->cig_id);
    });

    it('returns not found for reserved legacy tag image routes', function (): void {
        $this->get('/comm-links/images/tag-Constellation%20Phoenix')
            ->assertNotFound();

        $this->get('/comm-links/images/tag-Constellation%20Phoenix/similar')
            ->assertNotFound();
    });
});

describe('reverse search', function (): void {
    beforeEach(function (): void {
        $csrfToken = 'comm-link-reverse-search-csrf-token';

        $this->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken);
    });

    it('shows reverse image search results', function (): void {
        $image = Image::factory()->create([
            'src' => '/i/alpha/alpha.webp',
        ]);

        $image->metadata()->create([
            'size' => 4096,
            'mime' => 'image/webp',
            'last_modified' => now(),
        ]);

        $uploadedImage = UploadedFile::fake()->image('search.jpg', 32, 32);
        $contents = file_get_contents($uploadedImage->getPathname());
        expect($contents)->not->toBeFalse();

        $hashResult = app(PdqHasher::class)->hashContents($contents);

        ImageHash::query()->create([
            'comm_link_image_id' => $image->id,
            'pdq_hash' => $hashResult->toBitString(),
            'pdq_quality' => $hashResult->quality,
        ]);

        $response = $this->post(route('web.comm-links.images.reverse-search'), [
            'image' => $uploadedImage,
            'similarity' => 95,
        ]);

        $response->assertOk()
            ->assertSee('alpha.webp');
    });
});

describe('similar search', function (): void {
    it('returns redirect when not authenticated', function (): void {
        $image = Image::factory()->create();

        $response = $this->get(route('web.comm-links.images.similar', $image->id));

        $response->assertRedirect(route('login'));
    });

    it('renders similar search view contract when authenticated', function (): void {
        $user = User::factory()->create();
        $sharedHash = str_repeat('10', 128);

        $queryImage = Image::factory()->create([
            'alt' => 'Query Image',
        ]);
        $queryImage->hash()->create([
            'pdq_hash' => $sharedHash,
            'pdq_quality' => 100,
        ]);

        $similarImage = Image::factory()->create([
            'alt' => 'Similar Image',
        ]);
        $similarImage->hash()->create([
            'pdq_hash' => $sharedHash,
            'pdq_quality' => 100,
        ]);

        $commLink = CommLink::factory()->create([
            'cig_id' => 14001,
            'title' => 'Inside Star Citizen',
        ]);
        $commLink->images()->attach($similarImage->id);

        $response = $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', ['image' => $queryImage->id, 'similarity' => 95]));

        $response->assertOk()
            ->assertViewIs('comm-links.images.index')
            ->assertViewHas('searchType', 'similar-images')
            ->assertViewHas('searchQuery', fn (string $query): bool => str_contains($query, (string) $queryImage->id))
            ->assertViewHas('images', function (array $images) use ($similarImage, $commLink): bool {
                return count($images) === 1
                    && data_get($images, '0.id') === $similarImage->id
                    && data_get($images, '0.rsi_url') === $similarImage->url
                    && data_get($images, '0.comm_links.0.id') === $commLink->cig_id
                    && data_get($images, '0.comm_links.0.title') === $commLink->title;
            });
    });

    it('applies throttle:similar-image-search middleware to the similar images route', function (): void {
        $route = Route::getRoutes()->getByAction(CommLinkController::class.'@similarImages');

        expect($route)->not->toBeNull('Route for similarImages not found');
        expect($route->gatherMiddleware())->toContain('throttle:similar-image-search');
    });

    it('validates similarity parameter', function (mixed $similarity, bool $shouldSucceed): void {
        $user = User::factory()->create();
        $image = Image::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', ['image' => $image->id, 'similarity' => $similarity]));

        if ($shouldSucceed) {
            $response->assertOk()
                ->assertViewIs('comm-links.images.index');

            return;
        }

        $response->assertSessionHasErrors(['similarity']);
    })->with([
        [0, false],
        [1, true],
        [50, true],
        [100, true],
        [101, false],
        [-10, false],
        ['invalid', false],
        [75.5, false],
    ]);

    it('returns 404 when image id does not exist', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', 999999));

        $response->assertNotFound();
    });

    it('handles images without hash gracefully', function (): void {
        $user = User::factory()->create();
        $image = Image::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('web.comm-links.images.similar', $image->id));

        $response->assertOk()
            ->assertViewIs('comm-links.images.index')
            ->assertViewHas('images', fn (array $images): bool => $images === [])
            ->assertViewHas('searchType', 'similar-images')
            ->assertViewHas('searchQuery', fn (string $query): bool => str_contains($query, (string) $image->id));
    });
});
