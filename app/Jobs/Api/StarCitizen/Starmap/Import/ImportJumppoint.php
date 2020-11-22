<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Import;

use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class ParseJumppoint
 */
class ImportJumppoint implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Collection
     */
    protected Collection $rawData;

    /**
     * Create a new job instance.
     *
     * @param array|Collection $rawData
     */
    public function __construct($rawData)
    {
        $this->rawData = new Collection($rawData);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $data = $this->getData();

        Jumppoint::updateOrCreate(
            [
                'cig_id' => $data->pull('cig_id'),
            ],
            $data->toArray()
        );
    }

    public function getData(): Collection
    {
        return new Collection(
            [
                'cig_id' => $this->rawData->get('id'),
                'direction' => $this->rawData->get('direction'),
                'entry_id' => $this->rawData->get('entry_id'),
                'exit_id' => $this->rawData->get('exit_id'),
                'name' => $this->rawData->get('name'),
                'size' => $this->rawData->get('size'),
                'entry_status' => $this->rawData->get('entry')['status'] ?? '',
                'exit_status' => $this->rawData->get('exit')['status'] ?? '',
            ]
        );
    }
}
