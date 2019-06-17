<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 29.09.2018
 * Time: 14:51
 */

namespace Tests\Feature\Job\Api\StarCitizen\Vehicle\Parser;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseVehicle;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Type\Type;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ParseVehicleTest extends TestCase
{
    private $auroraES = <<<EOL
    {
        "afterburner_speed": "1140",
        "beam": "8.0",
        "cargocapacity": null,
        "chassis_id": "1",
        "description": "The Aurora is the modern-day descendant of the Roberts Space Industries X-7 spacecraft which tested the very first jump engines. Utilitarian to a T, the Aurora Essential is the perfect choice for new ship owners: versatile enough to tackle a myriad of challenges, yet with a straightforward and intuitive design.",
        "focus": "Starter / Pathfinder",
        "height": "4.0",
        "id": "1",
        "length": "18.0",
        "manufacturer": {
            "code": "RSI",
            "description": "The original creators of the engine that kickstarted humanity\u2019s expansion into space, Roberts Space Industries build a wide range of spaceships that serve all needs starting at basic interstellar travel to deep exploration on the outer edges of the galaxy. The tagline is \u201cRoberts Space Industries: Delivering the Stars since 2075\u201d",
            "id": "1",
            "known_for": "the Aurora and the Constellation",
            "media": [],
            "name": "Roberts Space Industries"
        },
        "manufacturer_id": "1",
        "mass": "25172",
        "max_crew": "1",
        "media": [],
        "min_crew": "1",
        "name": "Aurora ES",
        "pitch_max": "70.0",
        "production_note": null,
        "production_status": "flight-ready",
        "roll_max": "95.0",
        "scm_speed": "190",
        "size": "small",
        "time_modified": "1 year ago",
        "time_modified.unfiltered": "2017-10-24 19:40:49",
        "type": "multi",
        "url": "/pledge/ships/rsi-aurora/Aurora-ES",
        "xaxis_acceleration": "43.0",
        "yaw_max": "70.0",
        "yaxis_acceleration": "45.7",
        "zaxis_acceleration": "44.2"
    }
EOL;

    /**
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseVehicle
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Manufacturer
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\ProductionNote
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\ProductionStatus
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Type
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Focus
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Size
     * @covers \App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement
     */
    public function testParsing()
    {
        $parser = new ParseVehicle(new Collection(json_decode($this->auroraES, true)));
        $parser->handle();

        $this->assertDatabaseHas(
            'vehicles',
            [
                'name' => 'Aurora ES',
            ]
        )->assertDatabaseHas(
            'manufacturers',
            [
                'name_short' => 'RSI',
            ]
        );

        $this->assertCount(1, Ship::all());
        $this->assertCount(1, Manufacturer::all());
        $this->assertCount(2, ProductionStatus::all());
        $this->assertCount(1, ProductionNote::all());
        $this->assertCount(2, Focus::all());
        $this->assertCount(2, Type::all());
        $this->assertCount(2, Size::all());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call(
            'db:seed',
            [
                '--class' => 'ProductionNoteTableSeeder',
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => 'ProductionStatusTableSeeder',
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => 'SizeTableSeeder',
            ]
        );
        Artisan::call(
            'db:seed',
            [
                '--class' => 'TypeTableSeeder',
            ]
        );
    }
}
