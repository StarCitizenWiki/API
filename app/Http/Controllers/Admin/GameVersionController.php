<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game\GameVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GameVersionController extends Controller
{
    public function index(): View
    {
        $versions = GameVersion::query()
            ->orderBy('released_at', 'desc')
            ->get();

        return view('admin.game-versions.index', compact('versions'));
    }

    public function setDefault(GameVersion $gameVersion): RedirectResponse
    {
        DB::transaction(static function () use ($gameVersion) {
            GameVersion::query()->update(['is_default' => false]);
            $gameVersion->update([
                'is_default' => true,
                'is_hidden' => false,
            ]);
        });

        return redirect()->route('admin.game-versions.index')
            ->with('success', "Game version {$gameVersion->code} set as default.");
    }

    public function hide(GameVersion $gameVersion): RedirectResponse
    {
        if ($gameVersion->is_default) {
            return redirect()->route('admin.game-versions.index')
                ->with('error', "Game version {$gameVersion->code} is the default version and cannot be hidden.");
        }

        $gameVersion->update(['is_hidden' => true]);

        return redirect()->route('admin.game-versions.index')
            ->with('success', "Game version {$gameVersion->code} hidden from the selector.");
    }

    public function show(GameVersion $gameVersion): RedirectResponse
    {
        $gameVersion->update(['is_hidden' => false]);

        return redirect()->route('admin.game-versions.index')
            ->with('success', "Game version {$gameVersion->code} shown in the selector.");
    }
}
