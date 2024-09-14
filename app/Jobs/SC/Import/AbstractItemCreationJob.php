<?php

namespace App\Jobs\SC\Import;

use App\Services\Parser\SC\Labels;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class AbstractItemCreationJob implements ShouldQueue
{
    protected readonly Labels $labels;

    public function __construct(protected string $filePath) {}

    protected function loadLabels(): void
    {
        $this->labels = new Labels;
    }
}
