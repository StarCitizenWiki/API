<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\MeleeCombatConfig\MeleeCombatConfig;
use App\Services\Parser\SC\Weapon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class Knife extends AbstractItemCreationJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->loadLabels();
        try {
            $parser = new Weapon($this->filePath, $this->labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }
        $item = $parser->getData();
        if (empty($item['knife'])) {
            return;
        }

        $this->createConfig($item['knife']);

        \App\Models\SC\Char\PersonalWeapon\Knife::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'can_be_used_for_take_down' => $item['knife']['can_be_used_for_take_down'] ?? null,
            'can_block' => $item['knife']['can_block'] ?? null,
            'can_be_used_in_prone' => $item['knife']['can_be_used_in_prone'] ?? null,
            'can_dodge' => $item['knife']['can_dodge'] ?? null,
            'melee_combat_config_uuid' => $item['knife']['melee_combat_config'] ?? null,
        ]);
    }

    private function createConfig(array $data): void
    {
        collect($data['attack_config'])->each(function (array $config) use ($data) {
            /** @var MeleeCombatConfig $configModel */
            $configModel = MeleeCombatConfig::query()->firstOrCreate([
                'uuid' => $data['melee_combat_config'],
                'category' => $config['actionCategory'],
            ], [
                'stun_recovery_modifier' => $config['stunRecoveryModifier'],
                'block_stun_reduction_modifier' => $config['blockStunReductionModifier'],
                'block_stun_stamina_modifier' => $config['blockStunStaminaModifier'],
                'attack_impulse' => $config['attackImpulse'],
                'ignore_body_part_impulse_scale' => $config['ignoreBodyPartImpulseScale'],
                'fullbody_animation' => $config['fullbodyAnimation'],
            ]);

            collect($config['damageInfo'])->each(function (float $damage, string $key) use ($configModel) {
                $configModel->damages()->updateOrCreate([
                    'name' => strtolower(str_replace('Damage', '', $key)),
                ], [
                    'damage' => $damage,
                ]);
            });
        });
    }
}
