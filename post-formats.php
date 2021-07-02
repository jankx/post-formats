<?php
use Jankx\PostFormats\PostFormats;

// Ensure class Jankx\PostFormats\PostFormats is exists.
if (class_exists(PostFormats::class)) {
    PostFormats::get_instance();
}
