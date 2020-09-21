<?php declare(strict_types=1);

if (!function_exists('get_cache_key_for_current_request')) {
    /**
     * From https://laravel-news.com/cache-query-params
     * Generates a Hash based on the current URL, used by Cache
     *
     * @return string
     */
    function get_cache_key_for_current_request()
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return sha1($fullUrl);
    }
}

if (!function_exists('get_bootstrap_class_from_log_level')) {
    /**
     * @param string $level
     *
     * @return string
     */
    function get_bootstrap_class_from_log_level(string $level)
    {
        switch ($level) {
            case 'error':
            case 'critical':
                return 'danger';

            default:
                return $level;
        }
    }
}

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
                $chunks[$i] = implode('', (array) $chunk);
            }
            $tmp = $chunks;
        }

        return $tmp;
    }
}
