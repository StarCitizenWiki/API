<?php declare(strict_types = 1);

/**
 * Global Constants
 */
define('API_VERSION', "1.0");

define('SCW_URL', 'http://localhost/');

/** FundImageController */
define('FUNDIMAGE_FUNDING_ONLY', 'funding_only');
define('FUNDIMAGE_FUNDING_AND_TEXT', 'funding_and_text');
define('FUNDIMAGE_FUNDING_AND_BARS', 'funding_and_bars');
define('FUNDIMAGE_DISK_SAVE_PATH', 'tools_media_images');
define(
    'FUNDIMAGE_RELATIVE_SAVE_PATH',
    join(DIRECTORY_SEPARATOR, ['app', 'tools', 'media', 'images'.DIRECTORY_SEPARATOR])
);
define('FUNDIMAGE_CACHE_TIME', 600);

/** Auth */
define('AUTH_HOME', '/');
define('AUTH_ACCOUNT', '/account');
define('AUTH_LOGIN', '/login');

define('ADMIN_INTERNAL_PASSWORD', '4L4bgcM;i,Hw@l~a/&s\Yd;R`QdoH/at');

/** Throttling */
define('THROTTLE_PERIOD', 1);
define('THROTTLE_GUEST_REQUESTS', 10);

/** Transform Types */
define('TRANSFORM_COLLECTION', 'collection');
define('TRANSFORM_ITEM', 'item');
define('TRANSFORM_NULL', 'NullResource');

/** Piwik */
define('PIWIK_URL', 'https://piwik.octofox.de/');
define('PIWIK_SITE_ID', 15);

/** Short URL */
define('SHORT_URL_LENGTH', 6);

/** Log Break Points */
define('LOG_ERROR_DANGER_HOUR', 10);
define('LOG_ERROR_WARNING_HOUR', 5);
define('LOG_ERROR_DANGER_DAY', LOG_ERROR_DANGER_HOUR * 2);
define('LOG_ERROR_WARNING_DAY', LOG_ERROR_WARNING_HOUR * 2);

define('LOG_WARNING_DANGER_HOUR', 20);
define('LOG_WARNING_WARNING_HOUR', 10);
define('LOG_WARNING_DANGER_DAY', LOG_WARNING_DANGER_HOUR * 2);
define('LOG_WARNING_WARNING_DAY', LOG_WARNING_WARNING_HOUR * 2);
