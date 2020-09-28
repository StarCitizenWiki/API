<?php declare(strict_types=1);
/**
 * User: Keonie
 * Date: 09.09.2018 17:36
 */

namespace Tests\Feature\Job\Api\StarCitizen\Starmap\Parser;

use App\Jobs\Api\StarCitizen\Starmap\Parser\ParseStarSystem;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ParseStarsystemTest extends TestCase
{

    /**
     * @covers \App\Jobs\Api\StarCitizen\Starmap\Parser\ParseStarSystem
     * @covers \App\Jobs\Api\StarCitizen\Starmap\Parser\Affiliation
     */
    public function testParse()
    {
//        $job = new DownloadStarmap(false);
//        $job->handle();

//        $job = new ParseStarmapDownload();
//        $job->handle();

        $affiliation[] = [
            'id' => "1",
            'name' => "UEE",
            'code' => "uee",
            'color' => "#48bbd4",
            'membership.id' => "741",
        ];

        $starsystem[] = [
            'id' => "314",
            'status' => "P",
            'time_modified' => "2018-01-27 01:36:42",
            'type' => "SINGLE_STAR",
            'name' => "Stanton",
            'code' => "STANTON",
            'position_x' => "49.53471800",
            'position_y' => "-2.63396450",
            'position_z' => "16.47529200",
            'description' => "While the UEE still controls the rights to the system overall, the four planets themselves were sold by the government to four megacorporations making them the only privately-owned worlds in the Empire. Though subject to the UEE’s Common Laws and standard penal code, the UEE does not police the region. Instead, private planetary security teams enforce the local law.",
            'info_url' => null,
            'affiliation' => $affiliation,
            'aggregated_size' => "4.85000000",
            'aggregated_population' => 10,
            'aggregated_economy' => 10,
            'aggregated_danger' => 10,
            'thumbnail' => [
                'slug' => "anxi4tr0ija81",
                'source' => "https://robertsspaceindustries.com/media/anxi4tr0ija81r/source/JStanton-Arccorp.jpg",
            ],
        ];

        $parseStarsystems = new ParseStarSystem(new Collection($starsystem[0]));
        $parseStarsystems->handle();
        $this->assertEquals(1, Starsystem::count());
        $this->assertEquals(1, Affiliation::count());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
    }
}
