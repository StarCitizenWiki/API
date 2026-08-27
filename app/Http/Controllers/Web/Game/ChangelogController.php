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
    // Above this size the row renders a change count (computed in SQL) without the detail tree.
    private const int MAX_RENDERABLE_DIFF_BYTES = 262144;

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
            ->select('id', 'from_version_id', 'to_version_id', 'entity_type', 'entity_id', 'change_type', 'column_changes')
            ->selectRaw("CASE WHEN json_typeof(data_changes) = 'object' THEN (SELECT count(*) FROM json_object_keys(data_changes)) ELSE 0 END AS data_changes_count")
            ->selectRaw('octet_length(data_changes::text) AS data_changes_bytes')
            ->orderByRaw("CASE change_type WHEN 'removed' THEN 1 WHEN 'added' THEN 2 WHEN 'modified' THEN 3 END");

        if ($changeType !== 'all') {
            $query->where('change_type', $changeType);
        }

        $changes = $query->paginate(50)->appends($request->query());

        $renderableIds = $changes->getCollection()
            ->filter(fn (VersionDiff $diff): bool => (int) ($diff->data_changes_bytes ?? 0) <= self::MAX_RENDERABLE_DIFF_BYTES)
            ->pluck('id');

        if ($renderableIds->isNotEmpty()) {
            $hydrated = VersionDiff::query()
                ->whereIn('id', $renderableIds)
                ->pluck('data_changes', 'id');

            $changes->getCollection()
                ->each(fn (VersionDiff $diff) => $diff->setAttribute('data_changes', $hydrated[$diff->id] ?? null));
        }

        $diffVersionIds = VersionDiff::query()
            ->selectRaw('DISTINCT to_version_id')
            ->pluck('to_version_id');

        $allVersionCodes = GameVersion::query()
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
