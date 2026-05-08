<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\Game\GameVersion;
use App\Models\Game\VersionDiff;
use App\Support\Game\EntityTypeConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

#[CacheTag('gameversions')]
class ChangelogController extends Controller
{
    public function show(Request $request, string $version): View
    {
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

        return view('changelog.show', [
            'version' => $gameVersion,
            'previousVersion' => $previousVersion,
            'changes' => $changes,
            'changeCounts' => $changeCounts,
            'entityTypes' => EntityTypeConfig::all(),
            'entityType' => $entityType,
            'changeType' => $changeType,
            'pageTitle' => "Changelog: {$previousVersion->code} → {$gameVersion->code}",
        ]);
    }
}
