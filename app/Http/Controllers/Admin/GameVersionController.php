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
            $gameVersion->update(['is_default' => true]);
        });

        return redirect()->route('admin.game-versions.index')
            ->with('success', "Game version {$gameVersion->code} set as default.");
    }
}
