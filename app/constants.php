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

define('AUTH_ADMIN_IDS', [1]);


/** Throttling */
define('THROTTLE_PERIOD', 1);
define('THROTTLE_GUEST_REQUESTS', 10);

/** Domains */
define('TOOLS_DOMAIN', 'tools.star-citizen.wiki');
define('API_DOMAIN', 'api.star-citizen.wiki');

/** Transform Types */
define('TRANSFORM_COLLECTION', 1);
define('TRANSFORM_ITEM', 2);
