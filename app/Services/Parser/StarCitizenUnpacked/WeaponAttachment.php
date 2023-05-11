<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

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
            'item_type' => $data['item_type'] ?? null,
            'attachment_point' => $data['attachment_point'] ?? null,
            'magnification' => $data['magnification'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'utility_class' => $data['utility_class'] ?? null,
            'ammo' => $this->loadAmmoData(),
        ];
    }

    private function loadAmmoData(): array
    {
        $ammo = $this->get('SAmmoContainerComponentParams', []);
        if (empty($ammo)) {
            return [];
        }

        return [
            'initial_ammo_count' => $ammo['initialAmmoCount'],
            'max_ammo_count' => $ammo['maxAmmoCount'],
        ];
    }
}
