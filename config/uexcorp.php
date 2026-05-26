<?php

declare(strict_types=1);

return [

    'api_url' => env('UEXCORP_API_URL', 'https://api.uexcorp.uk/2.0'),

    'terminal_location_overrides' => [
        'Area 18' => 'Area18',
        'Deakins Research' => 'Deakins Research Outpost',
        'Shady Glen' => 'Shady Glen Farms',
        'GrimHEX' => 'Grim HEX',
        'Dudley  Daughters' => 'Dudley & Daughters',
        'Dudley Daughters' => 'Dudley & Daughters',
    ],

    'item_uuid_overrides' => [
        '5d6c1c28-1589-4c72-8cc3-ff90f998dca3' => '02d4cd2e-fa98-4086-aee1-6b2dfce8ea27',
    ],

    'vehicle_uuid_overrides' => [
        // Aurora Mk I variants (UEX UUIDs differ from wiki)
        '42b72b92-7880-446d-8770-b158e4b0fd10' => 'd8987dc2-340d-4312-8d4f-aee7e7fac823',
        'c596f592-1196-4378-8583-18ff13962b85' => 'a6e1bb18-8a11-4bbc-865e-45fd84e7b469',
        'bb8e3cdf-7f35-4bdb-bc2d-a15b8b859359' => '6fa21dc8-c838-4134-a74a-0cd8313bcad8',
        '7cab9bbc-3d67-4ee7-99ef-fe41991f8a3b' => 'c984fac2-06c0-4776-8f93-07ebd2c26ad3',
        '984283ce-dd5f-4807-a7b0-f6fbf053716c' => 'd7828052-56fb-41a6-ae7d-bc29b0bdf68c',
        // Prowler / Prowler Utility share UEX UUID
        '087b2253-2fc7-4fae-9f9d-97f64b7c43db' => '528fdddc-c97f-4ae8-a00e-11a59fc3662d',
        // Guardian QI / Guardian MX share UEX UUID
        'e98521b6-ea2f-4c10-9050-abc29d89d0e8' => 'aaa0cdcd-907c-4533-b3fc-181ede06076d',
        // F7A Hornet Mk II
        '26c25ff6-fd3c-48f5-babc-4018ef780031' => '6759360c-8b2f-4a89-b7c6-82e94b873c1b',
        // Idris-M
        '797201fa-ca9a-48b1-abcb-48b6d4d86a37' => 'f0caa993-4c6b-4402-8ef1-d91879060f3b',
        // Polaris
        '565ffdd6-8122-4c24-a690-85aa5aade0ff' => 'a5a5b055-c5d7-4384-9951-f15d47b88789',
        // Valkyrie Liberator Edition
        '4e62ae95-d173-4b94-8b91-9d85bc963ba1' => '6f0e14a9-cb6c-4b71-ae92-376867bb1be8',
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
        // Empty UEX UUIDs
        'Ares Inferno Starfighter' => '04571d49-0680-4b84-bdb1-dbebeb22c898',
        'Ares Ion Starfighter' => '30aab266-9131-4a07-808b-61c868cb404f',
        '600i Executive Edition' => '38ae749f-d4fc-4dfd-907b-ed8db61f3d88',
        'ATLS IKTI' => '4a7a4b9d-ae3c-4375-af63-4100f831f43c',
        'ATLS IKTI Rad' => 'a13e7de9-3c22-4981-86ec-6b38394d1b10',
        'Aurora Mk I SE' => '8e27d568-f424-4dc9-a10f-ca4c34efab85',
        'Aurora Mk II' => 'ec956be2-8e3a-460d-b3a8-97df4cb12b78',
        'Ballista Dunestalker' => '50f551e3-3435-4cbe-9ecf-8712855a8e27',
        'Ballista Snowblind' => 'aaeb976b-7a79-4a9c-91ee-07ed05d4a399',
        'Clipper' => 'c03e7aef-6411-4e85-9c10-e89862884433',
        'F7C-M Super Hornet Heartseeker Mk I' => 'b140e32f-a96b-462f-8c7b-e7243577b2f4',
        'Gladius Pirate' => 'b3d87a94-6858-4d3c-ba59-1ef29d727d1a',
        'Golem Ox' => 'ea2c22bc-c2f0-42dc-967d-3300f9232f91',
        'Hammerhead Best In Show Edition' => '0b17b11f-4ac4-4e04-b973-beb6f299b99b',
        'Hermes' => '4ed10b1c-df4f-4ab0-430e-b5b9f33b0f94',
        'Hull B' => '0d690d1a-9f57-4976-bff1-ec46bf039e75',
        'Idris-P' => '4b0585b0-b60b-4c52-a744-d812e5e9ad58',
        'Ironclad' => 'c42bf65e-f3f2-4234-8c19-8276bfd5de3a',
        'Ironclad Assault' => '4481572d-66c8-4f7a-90c3-0150052fde10',
        'L-22 Alpha Wolf' => 'df2166dd-70ba-4b10-90ee-bb2ae203b3e8',
        'M80' => '01c24704-ae47-4b31-931c-d35655edaa05',
        'MDC' => '3e911bb5-1a94-4501-bd63-560557f1b492',
        'MOLE Talus Edition' => '31eaaac4-b1ee-4430-a01c-b4ef0470715d',
        'MOTH' => 'aaa12a00-a1e7-4dfc-8626-3bebb005209f',
        'MULE' => 'fb484074-f33f-4977-a052-2937e8508acc',
        'Nova Tank' => 'b8a352bb-74a8-415b-9e08-77f684ac91b8',
        'Perseus' => '40a1f83b-3623-49e6-8168-ab8223998aa2',
        'Pitbull' => '6585ae24-738f-42d2-8d3e-eff4a21eaca9',
        'Reclaimer Best In Show Edition' => '94d31d2e-5fa8-460c-9fa4-2942b6f0ce33',
        'Retaliator' => '93e1a434-f4fa-43ee-a40c-71bcae5073bc',
        'Salvation' => '83318d44-b6c1-42d8-bcd1-284318718b42',
        'San tok.Yāi' => '9634b4d6-9f5a-4ad2-8574-431c15e3bc5d',
        'Starlite' => '6c44bcd6-d4c4-41c7-8939-a576e3aa9482',
        'Stinger' => 'f7b8addd-9253-495c-a278-bdaa236e6cbc',
        'Tiburon' => '58544d8c-f000-47a4-9ec6-ad663dbd8ff0',
        'UTV' => 'bfdf94df-32fc-497c-b9fa-c98f3fa4c83b',
    ],

    'item_name_to_uuid_overrides' => [],
];
