<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Item implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $itemModel = \App\Models\StarCitizenUnpacked\Item::updateOrCreate([
            'uuid' => $this->data['uuid'],
        ], [
            'name' => $this->data['name'],
            'type' => $this->data['type'],
            'sub_type' => $this->data['sub_type'],
            'manufacturer' => $this->data['manufacturer'],
            'size' => $this->data['size'],
            'class_name' => $this->data['class_name'],
            'version' => config('api.sc_data_version'),
        ]);

        $itemModel->volume()->updateOrCreate([
            'item_uuid' => $this->data['uuid'],
            'override' => 0,
        ], [
            'width' => $this->data['dimension']['width'],
            'height' => $this->data['dimension']['height'],
            'length' => $this->data['dimension']['length'],

            'volume' => $this->data['volume'],
        ]);

        if ($this->data['dimension_override']['width'] !== null) {
            $itemModel->volume()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
                'override' => 1,
            ], [
                'width' => $this->data['dimension_override']['width'],
                'height' => $this->data['dimension_override']['height'],
                'length' => $this->data['dimension_override']['length'],
                'volume' => 0,
            ]);
        }

        if ($this->data['inventory_container']['scu'] !== null) {
            $itemModel->container()->updateOrCreate([
                'item_uuid' => $this->data['uuid'],
            ], [
                'width' => $this->data['inventory_container']['width'],
                'height' => $this->data['inventory_container']['height'],
                'length' => $this->data['inventory_container']['length'],
                'scu' => $this->data['inventory_container']['scu'],
            ]);
        }

        if (!empty($this->data['ports'])) {
            collect($this->data['ports'])->each(function (array $port) use ($itemModel) {
                $itemModel->ports()->updateOrCreate([
                    'name' => $port['name'],
                ], [
                    'display_name' => $port['display_name'],
                    'min_size' => $port['min_size'],
                    'max_size' => $port['max_size'],
                    'position' => $port['position'],
                ]);
            });
        }
    }
}
