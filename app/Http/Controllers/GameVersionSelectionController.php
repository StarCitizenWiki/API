<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SelectGameVersionRequest;
use App\Models\Game\GameVersion;
use Illuminate\Http\RedirectResponse;

class GameVersionSelectionController extends Controller
{
    public function __invoke(SelectGameVersionRequest $request): RedirectResponse
    {
        $versionCode = $request->string('version')->toString();

        $gameVersion = GameVersion::query()
            ->whereRaw('LOWER(code) = ?', [strtolower($versionCode)])
            ->firstOrFail();

        $request->session()->put('game_version_code', $gameVersion->code);

        $redirectUrl = $this->resolveRedirectUrl($request->input('redirect'));

        return redirect()->to(url()->query($redirectUrl, ['version' => $gameVersion->code]));
    }

    protected function resolveRedirectUrl(?string $redirect): string
    {
        $redirect = $redirect ?: url()->previous();

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $redirectHost = parse_url($redirect, PHP_URL_HOST);

        if ($redirectHost === null) {
            $redirect = '/'.ltrim($redirect, '/');

            return url($redirect);
        }

        if ($redirectHost === $appHost) {
            return $redirect;
        }

        return url('/');
    }
}
