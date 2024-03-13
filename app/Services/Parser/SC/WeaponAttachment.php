<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

final class WeaponAttachment extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();

        if ($attachDef === null) {
            return null;
        }

        $name = $this->getName($attachDef, 'Unknown Weapon Attachment');

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'item_type',
            'Type' => 'type',
            'Attachment Point' => 'attachment_point',
            'Magnification' => 'magnification',
            'Capacity' => 'capacity',
            'Class' => 'utility_class',
        ]);

        $data['attachment_point'] = $data['attachment_point'] ?? null;
        $data['item_type'] = $data['item_type'] ?? null;

        if ($attachDef['SubType'] === 'IronSight' && empty($data['attachment_point'])) {
            $data['attachment_point'] = 'Optic';
            $data['type'] = 'IronSight';
        }

        if (empty($data['type'])) {
            $data['type'] = $attachDef['SubType'];
        }

        if ($data['type'] === 'Magazine') {
            $data['attachment_point'] = 'Magazine Well';
        }

        if ($data['attachment_point'] === null && $data['type'] === 'Utility') {
            $data['attachment_point'] = 'Utility';
        }

        if ($data['attachment_point'] === 'Optic') {
            $data['item_type'] = 'Scope';
        }

        if ($data['attachment_point'] === null && $data['item_type'] === 'Ammo' && $data['type'] === 'Missile') {
            $data['attachment_point'] = 'Barrel';
        }

        return [
            'uuid' => $this->getUUID(),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),
            'name' => $name,
            'size' => $attachDef['Size'],
            'grade' => $attachDef['Grade'],
            'type' => $data['type'] ?? null,
            'sub_type' => $attachDef['SubType'],
            'item_type' => $data['item_type'] ?? null,
            'attachment_point' => $data['attachment_point'] ?? null,
            'magnification' => $data['magnification'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'utility_class' => $data['utility_class'] ?? null,
            'ammo' => $this->loadAmmoData(),
            'iron_sight' => $attachDef['SubType'] === 'IronSight' ? $this->loadIronSightData() : [],
        ];
    }

    private function loadAmmoData(): array
    {
        $ammo = $this->get('SAmmoContainerComponentParams', []);

        if (empty($ammo)) {
            return [];
        }

        $max = $ammo['maxAmmoCount'];
        if ($max == 0) {
            $max = $ammo['maxRestockCount'];
        }

        return [
            'ammunition_uuid' => $ammo['ammoParamsRecord'],
            'initial_ammo_count' => $ammo['initialAmmoCount'],
            'max_ammo_count' => $max,
        ];
    }

    private function loadIronSightData(): array
    {
        $aimModifier = $this->get('SWeaponModifierComponentParams.modifier.weaponStats.aimModifier', []);
        $zeroingParams = $this->get('SWeaponModifierComponentParams.zeroingParams.SWeaponZeroingParams', []);

        if (empty($aimModifier) && empty($zeroingParams)) {
            return [];
        }

        return [
            'default_range' => $zeroingParams['defaultRange'] ?? null,
            'max_range' => $zeroingParams['maxRange'] ?? null,
            'range_increment' => $zeroingParams['rangeIncrement'] ?? null,
            'auto_zeroing_time' => $zeroingParams['autoZeroingTime'] ?? null,
            'zoom_scale' => $aimModifier['zoomScale'] ?? null,
            'zoom_time_scale' => $aimModifier['zoomTimeScale'] ?? null,
        ];
    }
}
