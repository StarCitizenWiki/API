<?php

declare(strict_types=1);

namespace App\Traits;

use App\Contracts\HasChangelogsInterface;

trait CreateRelationChangelogTrait
{
    /**
     * Creates a new changelog for synced templates, tags and related articles
     *
     * @param array $relationSyncData Array containing the return values of ->sync(). E.g.: ['groupName' => 'sync Return']
     * @param HasChangelogsInterface $model The model
     */
    protected function createRelationChangelog(array $relationSyncData, HasChangelogsInterface $model): void
    {
        $changedData = collect($relationSyncData)
            ->map(function (array $group) {
                unset($group['updated']);

                return $group;
            })
            ->map(function (array $group) {
                return [
                    'old' => $group['detached'],
                    'new' => $group['attached'],
                ];
            })
            ->filter(function (array $group) {
                return !empty($group['old']) || !empty($group['new']);
            })
            ->toArray();

        if (empty($changedData)) {
            return;
        }

        $model->changelogs()->create([
            'type' => 'update',
            'changelog' => [
                'changes' => $changedData,
            ],
            'user_id' => 0,
        ]);
    }
}
