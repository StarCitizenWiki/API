<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\System\ModelChangelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

trait DiffTranslationChangelogTrait
{
    /**
     * Modifies a collection of changelogs to include diffed texts of changed translations
     *
     * @param Collection $collection
     * @param Model $model
     * @return Collection
     */
    protected function diffTranslations(Collection $collection, Model $model): Collection
    {
        $model->textChanges = 0;

        $collection->each(
            static function (ModelChangelog $changelog) use ($model) {
                if (!isset($changelog->changelog['changes']['translation'])) {
                    return;
                }

                $model->textChanges++;

                $builder = new StrictUnifiedDiffOutputBuilder(
                    [
                        'collapseRanges' => true,
                        'commonLineThreshold' => 1,
                        'contextLines' => 0,
                        'fromFile' => $model->created_at->toString(),
                        'fromFileDate' => '',
                        'toFile' => $model->created_at->toString(),
                        'toFileDate' => '',
                    ]
                );

                $differ = new Differ($builder);

                $changelog->diff = ($differ->diff(
                    $changelog->changelog['changes']['translation']['old'],
                    $changelog->changelog['changes']['translation']['new'],
                ));
            }
        );

        return $collection->sortByDesc('created_at');
    }
}
