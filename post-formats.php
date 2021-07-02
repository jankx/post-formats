<?php
use Jankx\PostFormats\PostFormats;

// Ensure class Jankx\PostFormats\PostFormats is exists.
if (class_exists(PostFormats::class)) {
    add_action('after_setup_theme', array(PostFormats::class, 'get_instance'));
}

