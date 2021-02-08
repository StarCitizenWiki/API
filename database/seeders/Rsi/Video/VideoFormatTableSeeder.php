<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\Video;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoFormatTableSeeder extends Seeder
{
    private $formats = [
        '10 for the ... Series',
        'Anniversary',
        'Around the Verse',
        'Bugsmashers',
        'Calling All Devs',
        'Citizen of the Stars',
        'CitizenCon',
        'Cloud Imperium Games',
        'Commercials',
        'Community',
        'Discussion',
        'Game Commander',
        'Gameplay Development',
        'Gamescom',
        'Happy Hour',
        'Holiday Livestream',
        'Inside Star Citizen',
        'Loremaker\'s Guide to the Galaxy',
        'Meet the Devs',
        'Pillar Talk',
        'RSI Museum',
        'Reverse the Verse',
        'Reverse the Verse LIVE',
        'Ship Shape',
        'South by Southwest',
        'Squadron 42',
        'Star Citizen',
        'Star Citizen Live',
        'Subscriber\'s Town Hall',
        'Team Interviews',
        'Technical Presentation',
        'The Next Great Starship',
        'Trailer / Gameplay Trailer',
        'Wingman\'s Hangar',
        'Wonderful World of Star Citizen',
        'Xi\'an Language',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('video_formats')->insert(
            [
                'name' => 'Undefined',
                'slug' => 'undefined',
            ]
        );

        foreach ($this->formats as $format) {
            DB::table('video_formats')->insert(
                [
                    'name' => $format,
                    'slug' => Str::slug($format),
                ]
            );
        }
    }
}
