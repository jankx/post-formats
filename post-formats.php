<?php
use Jankx\PostFormats\PostFormats;

if (!defined('JANKX_POST_FORMATS_ROOT_DIR')) {
    define('JANKX_POST_FORMATS_ROOT_DIR', dirname(__FILE__));
}

// Ensure class Jankx\PostFormats\PostFormats is exists.
if (class_exists(PostFormats::class)) {
    add_action('after_setup_theme', array(PostFormats::class, 'get_instance'));
}

function jankx_post_formats_asset_url($path = '') {
    return sprintf(
        '%s/assets/%s',
        jankx_get_path_url(JANKX_POST_FORMATS_ROOT_DIR),
        $path
    );
}
