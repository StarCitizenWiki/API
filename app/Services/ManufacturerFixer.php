<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ManufacturerFixer
{
    private static ?Collection $manufacturers = null;

    public static function getByCode(string $code): ?array
    {
        self::init();

        return self::$manufacturers->firstWhere('code', $code);
    }

    public static function getByName(string $name): ?array
    {
        self::init();

        return self::$manufacturers->firstWhere('name', $name);
    }

    private static function init()
    {
        if (self::$manufacturers !== null) {
            return;
        }

        $data = Storage::get('manufacturers.json');
        $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        self::$manufacturers = collect($data);

        /** Some more manual stuff */
        self::$manufacturers->push([
            'code' => 'MISC',
            'name' => 'Musashi Industrial & Starflight Concern',
            'name_fix' => 'Musashi Industrial and Starflight Concern',
        ]);
        self::$manufacturers->push([
            'code' => 'BEHR',
            'name' => 'Behring',
            'name_fix' => 'Behring Applied Technology',
        ]);
    }
}
