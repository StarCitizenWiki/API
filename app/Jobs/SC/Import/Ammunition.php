<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Ammunition implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(readonly public array $data)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \App\Models\SC\Ammunition\Ammunition $ammunition */
        $ammunition = \App\Models\SC\Ammunition\Ammunition::query()->updateOrCreate([
            'uuid' => $this->data['ammunition']['uuid'],
        ], [
            'size' => $this->data['ammunition']['size'],
            'lifetime' => $this->data['ammunition']['lifetime'],
            'speed' => $this->data['ammunition']['speed'],
            'range' => $this->data['ammunition']['range'],
        ]);

        collect($this->data['ammunition']['damages'])->each(function ($damageClass) use ($ammunition) {
            collect($damageClass)->each(function ($damage) use ($ammunition) {
                $ammunition->damages()->updateOrCreate([
                    'type' => $damage['type'],
                    'name' => $damage['name'],
                ], [
                    'damage' => $damage['damage'],
                ]);
            });
        });

        $ammunition->piercability()->updateOrCreate([
            'ammunition_uuid' => $this->data['ammunition']['uuid'],
        ], [
            'damage_falloff_level_1' => $this->data['ammunition']['piercability']['damage_falloff_level_1'] ?? 0,
            'damage_falloff_level_2' => $this->data['ammunition']['piercability']['damage_falloff_level_2'] ?? 0,
            'damage_falloff_level_3' => $this->data['ammunition']['piercability']['damage_falloff_level_3'] ?? 0,
            'max_penetration_thickness' => $this->data['ammunition']['piercability']['max_penetration_thickness'] ?? 0,
        ]);

        collect($this->data['ammunition']['damage_falloffs'])->each(function ($falloff, $key) use ($ammunition) {
            $ammunition->damageFalloffs()->updateOrCreate([
                'type' => $key,
            ], [
                'physical' => $falloff['physical'] ?? 0,
                'energy' => $falloff['energy'] ?? 0,
                'distortion' => $falloff['distortion'] ?? 0,
                'thermal' => $falloff['thermal'] ?? 0,
                'biochemical' => $falloff['biochemical'] ?? 0,
                'stun' => $falloff['stun'] ?? 0,
            ]);
        });
    }
}
