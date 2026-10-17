<?php

declare(strict_types=1);

namespace App\Services\ImageHash;

final class PdqHash
{
    public const PDQHASH_NUM_SLOTS = 16;

    /**
     * @var array<int, int>
     */
    private array $slots;

    /**
     * @param  array<int, int>  $slots
     */
    private function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    public static function makeZeroesHash(): self
    {
        return new self(array_fill(0, self::PDQHASH_NUM_SLOTS, 0));
    }

    public function toHexString(): string
    {
        $string = '';
        for ($i = self::PDQHASH_NUM_SLOTS - 1; $i >= 0; $i--) {
            $string .= sprintf('%04x', $this->slots[$i]);
        }

        return $string;
    }

    public function toBitString(): string
    {
        $hex = $this->toHexString();
        $bits = '';

        $length = strlen($hex);
        for ($i = 0; $i < $length; $i++) {
            $bits .= str_pad(decbin(hexdec($hex[$i])), 4, '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    public function setBit(int $bitIndex): void
    {
        $slotIndex = intdiv($bitIndex, self::PDQHASH_NUM_SLOTS);
        $slotBitIndex = $bitIndex % self::PDQHASH_NUM_SLOTS;

        $this->slots[$slotIndex] |= 1 << $slotBitIndex;
    }
}
