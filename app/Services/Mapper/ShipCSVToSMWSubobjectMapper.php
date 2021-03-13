<?php

namespace App\Services\Mapper;


class ShipCSVToSMWSubobjectMapper
{
    private static string $format = <<<FORMAT
{{#subobject:
%s
}}
FORMAT;

    public static function mapShip(array $data): string
    {
        $string = collect($data)->map(function ($item, $key) {
            return sprintf(' |%s=%s', $key, $item);
        })
            ->implode("\n");

        return sprintf(self::$format, $string);
    }
}
