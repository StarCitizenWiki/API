<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Game\GameVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class AppShellComposer
{
    public function compose(View $view): void
    {
        try {
            $gameVersions = GameVersion::query()
                ->where('is_hidden', false)
                ->orderByDesc('is_default')
                ->orderByDesc('released_at')
                ->orderBy('code')
                ->get();

            $requestedCode = request()->query('version');
            $sessionCode = session('game_version_code');

            $selectedGameVersion = $this->matchVersionByCode($gameVersions, $requestedCode)
                ?? $this->matchVersionByCode($gameVersions, $sessionCode)
                ?? $gameVersions->firstWhere('is_default', true)
                ?? $gameVersions->first();

            $selectedGameVersionCode = $selectedGameVersion?->code;

            $view->with([
                'gameVersions' => $gameVersions,
                'selectedGameVersion' => $selectedGameVersion,
                'selectedGameVersionCode' => $selectedGameVersionCode,
            ]);
        } catch (\Exception $e) {
            // If the game_versions table doesn't exist (e.g., in tests),
            // provide empty values to avoid breaking the view
            $view->with([
                'gameVersions' => collect(),
                'selectedGameVersion' => null,
                'selectedGameVersionCode' => null,
            ]);
        }
    }

    protected function matchVersionByCode(Collection $versions, ?string $code): ?GameVersion
    {
        if ($code === null) {
            return null;
        }

        return $versions->first(function (GameVersion $version) use ($code) {
            return strtolower($version->code) === strtolower($code);
        });
    }
}
