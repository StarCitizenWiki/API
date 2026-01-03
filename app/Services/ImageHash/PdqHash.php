<?php

declare(strict_types=1);

namespace App\Services\ImageHash;

final class PdqHash
{
    public const PDQHASH_NUM_SLOTS = 16;

    public const PDQHASH_HEX_LENGTH = 64;

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

    /**
     * @throws MalformedPdqHashException
     */
    public static function fromHexString(string $hexString): self
    {
        if (strlen($hexString) !== self::PDQHASH_HEX_LENGTH) {
            throw new MalformedPdqHashException(
                $hexString,
                sprintf(
                    'Expected hash to have length %d, received hash with length %d',
                    self::PDQHASH_HEX_LENGTH,
                    strlen($hexString)
                )
            );
        }

        $binary = hex2bin($hexString);
        if ($binary === false) {
            throw new MalformedPdqHashException($hexString, 'Length is 64 but is not pure hexadecimal.');
        }

        $slots = unpack('n*', $binary);
        if ($slots === false) {
            throw new MalformedPdqHashException($hexString, 'Unable to unpack hash bytes.');
        }

        $orderedSlots = [];
        $k = self::PDQHASH_NUM_SLOTS - 1;
        foreach ($slots as $slot) {
            $orderedSlots[$k] = $slot;
            $k--;
        }

        return new self($orderedSlots);
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

    public function hammingDistanceTo(self $that): int
    {
        $sum = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $sum += self::popCount16($this->slots[$i] ^ $that->slots[$i]);
        }

        return $sum;
    }

    public function isWithinHammingDistanceOf(self $that, int $threshold): bool
    {
        $current = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $current += self::popCount16($this->slots[$i] ^ $that->slots[$i]);
            if ($current > $threshold) {
                return false;
            }
        }

        return true;
    }

    public function hammingNorm(): int
    {
        $sum = 0;
        for ($i = 0; $i < self::PDQHASH_NUM_SLOTS; $i++) {
            $sum += self::popCount16($this->slots[$i]);
        }

        return $sum;
    }

    private static function popCount16(int $value): int
    {
        $value -= (($value >> 1) & 0x5555);
        $value = ((($value >> 2) & 0x3333) + ($value & 0x3333));
        $value = ((($value >> 4) + $value) & 0x0F0F);
        $value += ($value >> 8);

        return $value & 0x1F;
    }
}
