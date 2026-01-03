<?php

declare(strict_types=1);

namespace App\Services\ImageHash;

use GdImage;
use RuntimeException;

final class PdqHasher
{
    private const LUMA_FROM_R_COEFF = 0.299;

    private const LUMA_FROM_G_COEFF = 0.587;

    private const LUMA_FROM_B_COEFF = 0.114;

    private const PDQ_JAROSZ_WINDOW_SIZE_DIVISOR = 128;

    private const PDQ_NUM_JAROSZ_XY_PASSES = 2;

    public function hashContents(string $contents): PdqHashResult
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is required for PDQ hashing.');
        }

        $image = self::readImageFromContents($contents, true);

        return self::computeHashAndQualityFromImage($image);
    }

    private static function readImageFromContents(string $contents, bool $downsampleFirst): GdImage
    {
        $originalImage = @imagecreatefromstring($contents);
        if (! $originalImage instanceof GdImage) {
            throw new RuntimeException('Image content could not be decoded.');
        }

        if (! $downsampleFirst) {
            return $originalImage;
        }

        $originalHeight = imagesy($originalImage);
        $originalWidth = imagesx($originalImage);

        if ($originalHeight <= 128 && $originalWidth <= 128) {
            return $originalImage;
        }

        $image = imagecreatetruecolor(128, 128);
        if (! $image instanceof GdImage) {
            throw new RuntimeException('Image downsampling failed.');
        }

        imagecopyresampled(
            $image,
            $originalImage,
            0,
            0,
            0,
            0,
            128,
            128,
            $originalWidth,
            $originalHeight
        );

        return $image;
    }

    /**
     * @return array<int, array<int, float>>
     */
    private static function imageToLumaMatrix(GdImage $image, int $numRows, int $numCols): array
    {
        $lumaMatrix = [];
        for ($i = 0; $i < $numRows; $i++) {
            $row = [];
            for ($j = 0; $j < $numCols; $j++) {
                $pixel = imagecolorat($image, $j, $i);
                $r = $pixel >> 16;
                $g = ($pixel >> 8) & 0xFF;
                $b = $pixel & 0xFF;
                $row[$j] = self::LUMA_FROM_R_COEFF * $r
                    + self::LUMA_FROM_G_COEFF * $g
                    + self::LUMA_FROM_B_COEFF * $b;
            }
            $lumaMatrix[$i] = $row;
        }

        return $lumaMatrix;
    }

    private static function computeJaroszFilterWindowSize(int $dimension): int
    {
        return (int) (($dimension + self::PDQ_JAROSZ_WINDOW_SIZE_DIVISOR - 1)
            / self::PDQ_JAROSZ_WINDOW_SIZE_DIVISOR);
    }

    /**
     * @param  array<int, array<int, float>>  $input
     * @param  array<int, array<int, float>>  $output
     */
    private static function boxAlongCols(array &$input, array &$output, int $numRows, int $numCols, int $windowSize): void
    {
        for ($j = 0; $j < $numCols; $j++) {
            $halfWindowSize = (int) (($windowSize + 2) / 2);

            $phase1Reps = $halfWindowSize - 1;
            $phase2Reps = $windowSize - $halfWindowSize + 1;
            $phase3Reps = $numRows - $windowSize;
            $phase4Reps = $halfWindowSize - 1;

            $leftIndex = 0;
            $rightIndex = 0;
            $outputIndex = 0;

            $sum = 0.0;
            $currentWindowSize = 0;

            for ($k = 0; $k < $phase1Reps; $k++) {
                $sum += $input[$rightIndex][$j];
                $currentWindowSize++;
                $rightIndex++;
            }

            for ($k = 0; $k < $phase2Reps; $k++) {
                $sum += $input[$rightIndex][$j];
                $currentWindowSize++;
                $output[$outputIndex][$j] = $sum / $currentWindowSize;
                $rightIndex++;
                $outputIndex++;
            }

            for ($k = 0; $k < $phase3Reps; $k++) {
                $sum += $input[$rightIndex][$j];
                $sum -= $input[$leftIndex][$j];
                $output[$outputIndex][$j] = $sum / $currentWindowSize;
                $leftIndex++;
                $rightIndex++;
                $outputIndex++;
            }

            for ($k = 0; $k < $phase4Reps; $k++) {
                $sum -= $input[$leftIndex][$j];
                $currentWindowSize--;
                $output[$outputIndex][$j] = $sum / $currentWindowSize;
                $leftIndex++;
                $outputIndex++;
            }
        }
    }

    /**
     * @param  array<int, array<int, float>>  $input
     * @param  array<int, array<int, float>>  $output
     */
    private static function boxAlongRows(array &$input, array &$output, int $numRows, int $numCols, int $windowSize): void
    {
        for ($i = 0; $i < $numRows; $i++) {
            $halfWindowSize = (int) (($windowSize + 2) / 2);

            $phase1Reps = $halfWindowSize - 1;
            $phase2Reps = $windowSize - $halfWindowSize + 1;
            $phase3Reps = $numCols - $windowSize;
            $phase4Reps = $halfWindowSize - 1;

            $leftIndex = 0;
            $rightIndex = 0;
            $outputIndex = 0;

            $sum = 0.0;
            $currentWindowSize = 0;

            for ($k = 0; $k < $phase1Reps; $k++) {
                $sum += $input[$i][$rightIndex];
                $currentWindowSize++;
                $rightIndex++;
            }

            for ($k = 0; $k < $phase2Reps; $k++) {
                $sum += $input[$i][$rightIndex];
                $currentWindowSize++;
                $output[$i][$outputIndex] = $sum / $currentWindowSize;
                $rightIndex++;
                $outputIndex++;
            }

            for ($k = 0; $k < $phase3Reps; $k++) {
                $sum += $input[$i][$rightIndex];
                $sum -= $input[$i][$leftIndex];
                $output[$i][$outputIndex] = $sum / $currentWindowSize;
                $leftIndex++;
                $rightIndex++;
                $outputIndex++;
            }

            for ($k = 0; $k < $phase4Reps; $k++) {
                $sum -= $input[$i][$leftIndex];
                $currentWindowSize--;
                $output[$i][$outputIndex] = $sum / $currentWindowSize;
                $leftIndex++;
                $outputIndex++;
            }
        }
    }

    /**
     * @param  array<int, array<int, float>>  $lumaMatrix
     */
    private static function jaroszFilter(
        array &$lumaMatrix,
        int $numRows,
        int $numCols,
        int $windowSizeRows,
        int $windowSizeCols
    ): void {
        $otherMatrix = [];
        for ($i = 0; $i < $numRows; $i++) {
            $row = array_fill(0, $numCols - 0, 0.0);
            $otherMatrix[$i] = $row;
        }

        for ($k = 0; $k < self::PDQ_NUM_JAROSZ_XY_PASSES; $k++) {
            self::boxAlongRows($lumaMatrix, $otherMatrix, $numRows, $numCols, $windowSizeRows);
            self::boxAlongCols($otherMatrix, $lumaMatrix, $numRows, $numCols, $windowSizeCols);
        }
    }

    /**
     * @param  array<int, array<int, float>>  $buffer64
     */
    private static function computeImageDomainQualityMetric(array $buffer64): int
    {
        $intGradientSum = 0;

        for ($i = 0; $i < 63; $i++) {
            for ($j = 0; $j < 64; $j++) {
                $u = $buffer64[$i][$j];
                $v = $buffer64[$i + 1][$j];
                $d = (int) ((($u - $v) * 100) / 255);
                $intGradientSum += (int) abs($d);
            }
        }

        for ($i = 0; $i < 64; $i++) {
            for ($j = 0; $j < 63; $j++) {
                $u = $buffer64[$i][$j];
                $v = $buffer64[$i][$j + 1];
                $d = (int) ((($u - $v) * 100) / 255);
                $intGradientSum += (int) abs($d);
            }
        }

        $quality = (int) ($intGradientSum / 90);

        return min($quality, 100);
    }

    /**
     * @param  array<int, array<int, float>>  $buffer64
     * @param  array<int, array<int, float>>  $buffer16x64
     * @param  array<int, array<int, float>>  $buffer16x16
     * @param  array<int, array<int, float>>  $dct16x64
     */
    private static function computeDct64To16(
        array &$buffer64,
        array &$buffer16x64,
        array &$buffer16x16,
        array &$dct16x64
    ): void {
        for ($i = 0; $i < 16; $i++) {
            for ($j = 0; $j < 64; $j++) {
                $sum = 0.0;
                for ($k = 0; $k < 64; $k++) {
                    $sum += $dct16x64[$i][$k] * $buffer64[$k][$j];
                }
                $buffer16x64[$i][$j] = $sum;
            }
        }

        for ($i = 0; $i < 16; $i++) {
            for ($j = 0; $j < 16; $j++) {
                $sum = 0.0;
                for ($k = 0; $k < 64; $k++) {
                    $sum += $buffer16x64[$i][$k] * $dct16x64[$j][$k];
                }
                $buffer16x16[$i][$j] = $sum;
            }
        }
    }

    /**
     * @param  array<int, array<int, float>>  $buffer16
     */
    private static function computeHashFromDctOutput(array $buffer16): PdqHash
    {
        $flatMatrix = [];
        for ($i = 0, $k = 0; $i < 16; $i++) {
            for ($j = 0; $j < 16; $j++, $k++) {
                $flatMatrix[$k] = $buffer16[$i][$j];
            }
        }

        sort($flatMatrix);
        $median = $flatMatrix[127];

        $hash = PdqHash::makeZeroesHash();
        for ($i = 0, $k = 0; $i < 16; $i++) {
            for ($j = 0; $j < 16; $j++, $k++) {
                if ($buffer16[$i][$j] > $median) {
                    $hash->setBit($k);
                }
            }
        }

        return $hash;
    }

    /**
     * @return array{0: array<int, array<int, float>>, 1: int}
     */
    private static function computeDctAndQualityFromImage(GdImage $image): array
    {
        $numRows = imagesy($image);
        $numCols = imagesx($image);

        $lumaMatrix = self::imageToLumaMatrix($image, $numRows, $numCols);

        $windowRows = self::computeJaroszFilterWindowSize($numCols);
        $windowCols = self::computeJaroszFilterWindowSize($numRows);
        self::jaroszFilter($lumaMatrix, $numRows, $numCols, $windowRows, $windowCols);

        $buffer64 = [];
        for ($i = 0; $i < 64; $i++) {
            $row = array_fill(0, 64, 0.0);
            $buffer64[$i] = $row;
        }

        for ($i = 0; $i < 64; $i++) {
            $ini = (int) ((($i + 0.5) * $numRows) / 64);
            for ($j = 0; $j < 64; $j++) {
                $inj = (int) ((($j + 0.5) * $numCols) / 64);
                $buffer64[$i][$j] = $lumaMatrix[$ini][$inj];
            }
        }

        $quality = self::computeImageDomainQualityMetric($buffer64);

        $buffer16x64 = [];
        for ($i = 0; $i < 16; $i++) {
            $row = array_fill(0, 64, 0.0);
            $buffer16x64[$i] = $row;
        }

        $buffer16x16 = [];
        for ($i = 0; $i < 16; $i++) {
            $row = array_fill(0, 16, 0.0);
            $buffer16x16[$i] = $row;
        }

        $dct16x64 = [];
        for ($i = 0; $i < 16; $i++) {
            $row = array_fill(0, 64, 0.0);
            $dct16x64[$i] = $row;
        }

        $matrixScale = sqrt(2.0 / 64.0);
        $pi = 3.141592653589793;
        for ($i = 0; $i < 16; $i++) {
            for ($j = 0; $j < 64; $j++) {
                $dct16x64[$i][$j] = $matrixScale *
                    cos(($pi / 2 / 64.0) * ($i + 1) * (2 * $j + 1));
            }
        }

        self::computeDct64To16($buffer64, $buffer16x64, $buffer16x16, $dct16x64);

        return [$buffer16x16, $quality];
    }

    private static function computeHashAndQualityFromImage(GdImage $image): PdqHashResult
    {
        [$buffer16, $quality] = self::computeDctAndQualityFromImage($image);

        return new PdqHashResult(self::computeHashFromDctOutput($buffer16), $quality);
    }
}
