<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\Game\GameVersion;
use App\Models\Game\VersionDiff;
use App\Support\Game\EntityTypeConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

#[CacheTag('gameversions')]
class ChangelogController extends Controller
{
    public function show(Request $request, string $version): View|RedirectResponse
    {
        $requestedVersion = $request->query('version');
        if ($requestedVersion !== null) {
            return redirect()->route('web.changelog.show', array_merge(
                ['version' => $requestedVersion],
                $request->only('entity_type', 'change_type'),
            ), 302);
        }

        $gameVersion = GameVersion::findByCode($version, fail: true);
        $previousVersion = $gameVersion->findPreviousVersion();
        abort_if($previousVersion === null, 404, 'No previous version found.');

        $fromId = $previousVersion->id;
        $toId = $gameVersion->id;

        $entityType = $request->input('entity_type', 'item');
        $changeType = $request->input('change_type', 'all');

        $changeCounts = VersionDiff::getChangeCounts($fromId, $toId);

        $config = EntityTypeConfig::get($entityType);
        abort_if($config === null, 404, 'Unknown entity type.');

        $query = VersionDiff::query()
            ->forVersionPair($fromId, $toId)
            ->with(['fromVersion', 'entity.data' => function ($q) use ($fromId, $toId): void {
                $q->whereIn('game_version_id', [$fromId, $toId]);
            }])
            ->where('entity_type', $config['model'])
            ->orderByRaw("CASE change_type WHEN 'removed' THEN 1 WHEN 'added' THEN 2 WHEN 'modified' THEN 3 END");

        if ($changeType !== 'all') {
            $query->where('change_type', $changeType);
        }

        $changes = $query->paginate(50)->appends($request->query());

        $diffVersionIds = VersionDiff::query()
            ->selectRaw('DISTINCT to_version_id')
            ->pluck('to_version_id');

        $allVersionCodes = GameVersion::query()
            ->where('is_hidden', false)
            ->whereIn('id', $diffVersionIds)
            ->orderBy('id')
            ->pluck('code')
            ->values();

        $currentIdx = $allVersionCodes->search($gameVersion->code);

        return view('changelog.show', [
            'version' => $gameVersion,
            'previousVersion' => $previousVersion,
            'changes' => $changes,
            'changeCounts' => $changeCounts,
            'entityTypes' => EntityTypeConfig::all(),
            'entityType' => $entityType,
            'changeType' => $changeType,
            'pageTitle' => "Changelog: {$previousVersion->code} -> {$gameVersion->code}",
            'olderVersionCode' => $currentIdx > 0 ? $allVersionCodes[$currentIdx - 1] : null,
            'newerVersionCode' => $allVersionCodes->has($currentIdx + 1) ? $allVersionCodes[$currentIdx + 1] : null,
        ]);
    }
}
