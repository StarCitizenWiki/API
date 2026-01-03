<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Import;

use App\Models\StarCitizen\Starmap\Affiliation;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Models\System\Language;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class ParseStarsytem
 */
class ImportStarsystem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Collection $rawData;

    /**
     * Create a new job instance.
     *
     * @param  array|Collection  $rawData
     */
    public function __construct($rawData)
    {
        $this->rawData = new Collection($rawData);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $systemData = $this->getData();
        $description = $systemData->pull('description');
        $affiliation = $systemData->pull('affiliation');

        /** @var Starsystem $starsystem */
        $starsystem = Starsystem::updateOrCreate(
            [
                'code' => $systemData->pull('code'),
            ],
            $systemData->toArray()
        );

        $starsystem->translations()->updateOrCreate(
            [
                'starsystem_id' => $starsystem->id,
                'locale_code' => Language::ENGLISH,
            ],
            [
                'translation' => $description,
            ]
        );

        $starsystem->affiliation()->sync($this->getAffiliationIds($affiliation));

        $this->dispatchCelestialObjectJobs();
    }

    public function getData(): Collection
    {
        return new Collection(
            [
                'cig_id' => $this->rawData->get('id'),
                'code' => $this->rawData->get('code'),

                'status' => $this->rawData->get('status'),

                'info_url' => $this->rawData->get('info_url'),

                'name' => $this->rawData->get('name'),
                'type' => $this->rawData->get('type'),

                'position_x' => $this->rawData->get('position_x'),
                'position_y' => $this->rawData->get('position_y'),
                'position_z' => $this->rawData->get('position_z'),

                'frost_line' => $this->rawData->get('frost_line'),
                'habitable_zone_inner' => $this->rawData->get('habitable_zone_inner'),
                'habitable_zone_outer' => $this->rawData->get('habitable_zone_outer'),

                'aggregated_size' => $this->rawData->get('aggregated_size'),
                'aggregated_population' => $this->rawData->get('aggregated_population'),
                'aggregated_economy' => $this->rawData->get('aggregated_economy'),
                'aggregated_danger' => $this->rawData->get('aggregated_danger'),

                'time_modified' => $this->rawData->get('time_modified'),

                'description' => trim(strip_tags(html_entity_decode($this->rawData->get('description')))),
                'affiliation' => $this->rawData->get('affiliation'),
            ]
        );
    }

    private function getAffiliationIds(array $affiliations): array
    {
        return collect($affiliations)
            ->filter(
                function ($affiliation) {
                    return isset($affiliation['id']);
                }
            )
            ->map(
                function ($affiliationData) {
                    return Affiliation::query()->updateOrCreate(['cig_id' => $affiliationData['id']], [
                        'name' => Arr::get($affiliationData, 'name'),
                        'code' => Arr::get($affiliationData, 'code'),
                        'color' => Arr::get($affiliationData, 'color'),
                        'membership_id' => Arr::get($affiliationData, 'membership.id', null),
                    ]);
                }
            )
            ->map(
                function (Affiliation $affiliation) {
                    return $affiliation->id;
                }
            )
            ->toArray();
    }

    private function dispatchCelestialObjectJobs(): void
    {
        collect($this->rawData->get('celestial_objects'))
            ->each(
                function (array $celestialObject) {
                    ImportCelestialObject::dispatch($celestialObject, $this->rawData->get('id'));
                }
            );
    }
}
