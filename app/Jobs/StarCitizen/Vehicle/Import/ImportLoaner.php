<?php

namespace App\Jobs\StarCitizen\Vehicle\Import;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ImportLoaner implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * https://github.com/fleetyards/fleetyards/blob/de56bf20cb105881426e7a1d98472c16e06715ef/app/lib/rsi/loaner_loader.rb
     * @var array|array[]
     */
    private array $modelsMap = [
        "100 Series" => ['100i', '125a', '135c'],
        "600i Series" => ["600i Touring", "600i Explorer", "600i Executive-Edition"],
        "Apollo" => ["Apollo Medivac", "Apollo Triage"],
        "Ares Ion / Inferno" => ["Ares Ion", "Ares Inferno"],
        "Carrack / Carrack Expedition" => ["Carrack"],
        "Carrack w/ C8X / Carrack Expedition w/C8X" => ["Carrack"],
        "Cyclone Variants" => ['Cyclone', 'Cyclone TR', 'Cyclone RN', 'Cyclone RC', 'Cyclone AA'],
        "Dragonfly" => ["Dragonfly Yellowjacket", "Dragonfly Black"],
        "G12A" => ["G12a"],
        "G12R" => ["G12r"],
        "Genesis Starliner" => ["Genesis"],
        "Hercules Starlifter (All)" => ["C2 Hercules", "M2 Hercules", "A2 Hercules"],
        "Hercules Starlifter A2" => ["A2 Hercules"],
        "Hull A & B" => ["Hull A", "Hull B"],
        "Hull D, E" => ["Hull D", "Hull E"],
        "Idris-M & P" => ['Idris-P', 'Idris-M'],
        "Kraken (+ Privateer)" => ["Kraken", "Kraken Privateer"],
        //"Mercury" => ["Mercury Star Runner"],
        "Mole" => ["Mole"],
        "Nox" => ["Nox", "Nox Kue"],
        "Reliant Variants" => ["Reliant Kore", "Reliant Mako", "Reliant Sen", "Reliant Tana"],
        "Retaliator" => ["Retaliator Bomber", "Retaliator"],
        "ROC (+ ROC DS)" => ['ROC', 'ROC-DS'],
        "San'Tok.yai" => ["San'tok.yāi"],
        "Spirit A1" => ["A1 Spirit"],
        "Spirit C1" => ["C1 Spirit"],
        "Spirit E1" => ["E1 Spirit"],
        "Talon & Talon Shrike" => ["Talon", "Talon Shrike"],
        "X1 & Variants" => ["X1", "X1 Velocity", "X1 Force"],
    ];

    private array $modelMap = [
        "85x" => "85X",
        "F7C - Hornet" => "F7C Hornet",
        "URSA Rover" => "Ursa Rover",
        "MPUV Passenger" => "MPUV Personnel",
        "Hercules C2" => "C2 Hercules",
        "Hercules M2" => "M2 Hercules",
        "Cyclone (Explorer only)" => "Cyclone",
        "Cyclone (Explorer only" => "Cyclone",
        "Khartu-al (Xi'an Scout)" => "Khartu-Al",
        "Khartu-al" => "Khartu-Al",
        "Mole" => "MOLE"
    ];

    private array $missing = [];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = Http::asJson()->get('https://support.robertsspaceindustries.com/api/v2/help_center/en-us/articles/360003093114');
        $data = $data->json('article.body');

        $crawler = new Crawler();
        $crawler->addHtmlContent($data);

        $rows = $crawler->filter('table tbody tr')->each(function (Crawler $crawler) {
            return [
                'ship' => trim($crawler->filter('td:first-child')->text()),
                'loaners' => $crawler->filter('td:last-child')->text(),
            ];
        });
        $version = $this->getLastUpdateVersion($crawler);

        if (empty($version)) {
            $this->fail('Could not find current version.');
        }

        collect($rows)->flatMap(function (array $data) {
            $name = $this->modelsMap[$data['ship']] ?? $data['ship'];
            $loaners = collect(explode(',', $data['loaners']))->map(function ($loaner) {
                $loaner = str_replace('and', '', $loaner);
                return preg_replace('/^\W*(.*?)\W*$/', '$1', $loaner);
            });

            $loaners = collect($loaners)->map(function (string $loaner) {
                return $this->modelMap[$loaner] ?? $loaner;
            })->toArray();

            if (is_array($name)) {
                return collect($name)->map(function ($ship) use ($loaners) {
                    return [
                        'ship' => $ship,
                        'loaners' => $loaners,
                    ];
                });
            }

            return [[
                'ship' => $name,
                'loaners' => $loaners,
            ]];
        })->each(function (array $datum) use ($version) {
            $name = $datum['ship'];

            try {
                /** @var Vehicle $vehicle */
                $vehicle = Vehicle::query()->where('name', 'LIKE', "%{$name}%")->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->missing[] = $name;
                return;
            }

            $loanerIDs = collect($datum['loaners'])->map(function (string $loaner) {
                try {
                    return Vehicle::query()->where('name', 'LIKE', "%{$loaner}%")->firstOrFail()->id;
                } catch (ModelNotFoundException $e) {
                    $this->missing[] = $loaner;
                }
            })
                ->filter()
            ->mapWithKeys(function ($id) use ($version) {
                return [ $id => [
                    'version' => $version,
                ]];
            });

            $vehicle->loaner()->sync($loanerIDs);
        });

        $this->missing = collect($this->missing)->unique()->toArray();

        if (!empty($this->missing)) {
            app('Log')::info(sprintf(
                'Found missing vehicles while importing loaner: %s',
                implode(', ', $this->missing)
            ));
        }
    }

    private function getLastUpdateVersion(Crawler $crawler): ?string
    {
        $version = $crawler->filter('p')->each(function (Crawler $crawler) {
            if (str_contains($crawler->text(), 'Last Update')) {
                return $crawler->text();
            }
        });

        $version = Arr::first(array_filter($version)) ?? null;

        if ($version === null) {
            return null;
        }

        $parts = array_map('trim', explode('|', $version));

        $version = strtoupper($parts[1] ?? '');

        return empty($version) ? null : $version;
    }
}
