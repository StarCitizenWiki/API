<?php declare(strict_types = 1);

if (!function_exists('make_name_readable')) {
    /**
     * @param string $methodName name of view function
     *
     * @return string
     */
    function make_name_readable(string $methodName): String
    {
        $readableName = preg_replace(
            '/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]|[0-9]{1,}/',
            ' $0',
            $methodName
        );

        return ucfirst(strtolower($readableName));
    }
}

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
