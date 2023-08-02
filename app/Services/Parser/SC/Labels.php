<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Labels
{
    private Collection $labels;

    /**
     * Labels contain all available translations
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(scdata('labels.json'));
        $this->labels = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
    }

    public function getData(): Collection
    {
        return $this->labels;
    }
}
