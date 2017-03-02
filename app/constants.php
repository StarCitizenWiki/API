<?php

/**
 * Global Constants
 */

/** FundImageController */
define('FUNDIMAGE_FUNDING_ONLY',  'funding_only');
define('FUNDIMAGE_FUNDING_AND_TEXT',  'funding_and_text');
define('FUNDIMAGE_FUNDING_AND_BARS',  'funding_and_bars');
define('FUNDIMAGE_DISK_SAVE_PATH', 'tools_media_images');
define('FUNDIMAGE_RELATIVE_SAVE_PATH', join(DIRECTORY_SEPARATOR, array('app', 'tools', 'media', 'images'.DIRECTORY_SEPARATOR)));
define('FUNDIMAGE_CACHE_TIME', 600);

/** Auth */
define('AUTH_HOME', '/');
define('AUTH_ACCOUNT', '/account');
define('AUTH_LOGIN', '/login');
define('AUTH_KEY_FIELD_NAME', 'key');

define('AUTH_ADMIN_IDS', [1]);


/** Throttling */
define('THROTTLE_PERIOD', 1);
define('THROTTLE_GUEST_REQUESTS', 10);

/** Domains */
define('TOOLS_DOMAIN', 'tools.star-citizen.wiki');
define('API_DOMAIN', 'api.star-citizen.wiki');
define('SHORT_URL_DOMAIN', 'rsi.im');

/** Transform Types */
define('TRANSFORM_COLLECTION', 'collection');
define('TRANSFORM_ITEM', 'item');
define('TRANSFORM_NULL', 'NullResource');

/** Piwik */
define('PIWIK_URL', 'https://piwik.octofox.de/');
define('PIWIK_SITE_ID', 15);

/** Short URL */
define('SHORT_URL_LENGTH', 6);
