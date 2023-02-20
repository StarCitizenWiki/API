<?php

declare(strict_types=1);


if (!function_exists('str_split_unicode')) {
    /**
     * Splits a Unicode String into the given length chunks.
     *
     * @param string $str
     * @param int    $length
     *
     * @return array|array[]|false|string[]
     */
    function str_split_unicode(string $str, int $length = 1)
    {
        $tmp = preg_split('~~u', $str, -1, PREG_SPLIT_NO_EMPTY);
        if ($length > 1) {
            $chunks = array_chunk($tmp, $length);
            foreach ($chunks as $i => $chunk) {
                $chunks[$i] = implode('', (array)$chunk);
            }
            $tmp = $chunks;
        }

        return $tmp;
    }
}
