<?php

declare(strict_types=1);

namespace App\Support\Game;

/**
 * SC-Unpacked group names under which mineable deposits are recorded.
 */
final class MiningGroups
{
    public const string SHIP = 'SpaceShip_Mineables';

    /** @var list<string> */
    public const array FPS = ['FPS_Mineables', 'FPS mineables'];

    /** @var list<string> */
    public const array HARVESTABLE = ['Harvestables', 'Havestables', 'Plants'];

    /** SC-Unpacked payloads misspell "Harvestables" as "Havestables". */
    public const array TYPO_ALIASES = ['Havestables' => 'Harvestables'];
}
