<?php

declare(strict_types=1);

return [

    'api_url' => env('UEXCORP_API_URL', 'https://api.uexcorp.uk/2.0'),

    'terminal_location_overrides' => [
        'Area 18' => 'Area18',
        'Deakins Research' => 'Deakins Research Outpost',
        'Shady Glen' => 'Shady Glen Farms',
    ],

    'item_uuid_overrides' => [
        '5d6c1c28-1589-4c72-8cc3-ff90f998dca3' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27',
    ],

    'vehicle_uuid_overrides' => [
        '42b72b92-7880-446d-8770-b158e4b0fd10' => 'd8987dc2-340d-4312-8d4f-aee7e7fac823',
        'c596f592-1196-4378-8583-18ff13962b85' => 'a6e1bb18-8a11-4bbc-865e-45fd84e7b469',
        'bb8e3cdf-7f35-4bdb-bc2d-a15b8b859359' => '6fa21dc8-c838-4134-a74a-0cd8313bcad8',
        '7cab9bbc-3d67-4ee7-99ef-fe41991f8a3b' => 'c984fac2-06c0-4776-8f93-07ebd2c26ad3',
        '984283ce-dd5f-4807-a7b0-f6fbf053716c' => 'd7828052-56fb-41a6-ae7d-bc29b0bdf68c',
        '087b2253-2fc7-4fae-9f9d-97f64b7c43db' => '528fdddc-c97f-4ae8-a00e-11a59fc3662d',
        'e98521b6-ea2f-4c10-9050-abc29d89d0e8' => 'aaa0cdcd-907c-4533-b3fc-181ede06076d',
    ],

    'vehicle_name_to_uuid_overrides' => [
        'C8R Pisces Rescue' => '1bed9058-e284-4c0f-b561-6ba57ab4f99d',
        'Khartu-al' => '8f61dc58-74ca-4dc6-aa95-c3105f10bba4',
        'C2 Hercules Starlifter' => '7533ff77-836c-4bd2-9171-487d9c63863c',
        'Mercury Star Runner' => 'c8e10f09-38a0-4730-9dd3-eb0a510c9e43',
        'A2 Hercules Starlifter' => '3bee9f2d-4494-4560-b9f8-463bc90695cb',
        'M2 Hercules Starlifter' => '5cf39b3b-41d8-45c1-8ef2-d4678c7a7b85',
        'MPUV Cargo' => 'e19fc6c3-f5fa-4623-9a17-b00a29b71500',
        'MPUV Personnel' => 'e17ccf62-f2d2-4c4e-a132-a3000d40fbde',
        'ATLS' => '09c9fa95-5b5a-4200-9ff8-3b288df9925b',
        'Ursa Medivac' => 'ebf015be-7bf9-44e9-ad9c-b5c65f00c530',
        'MPUV Tractor' => '674e0a52-4469-4b55-ba0d-6aecc44d1728',
        'CSV-SM' => 'e8efe30c-5a1e-4ca2-a956-424b352f80c2',
        'ATLS GEO' => 'a10bcff6-7985-431f-b0ea-bdb51e6d3f98',
        'Golem' => 'b616b3ad-123c-40f2-80bd-b8f4109633aa',
        'Starlancer TAC' => '14b8caeb-d5e1-4816-9537-86ef19afada8',
        'Meteor' => '3155e6fd-e1c8-4c15-9bd1-f8e3c2c7174f',
        'L-21 Wolf' => '1b201ef2-b4dd-46c2-978e-47a13e0a7f81',
        'Apollo Medivac' => '4559fbe2-55ab-a5a8-4e77-75106cb96ab7',
        'Apollo Triage' => '478bfc10-9577-ac19-262a-d40a93c3fcb2',
        'Shiv' => '3a7bfe4f-7a6c-4818-aba8-0f1019c3a647',
        'Paladin' => 'b617902a-6cab-457b-9eb8-494274f659b8',
    ],

];
