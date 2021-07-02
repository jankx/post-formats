<?php
namespace Jankx\PostFormats;

use Jankx\PostFormats\Format\VideoFormat;

class PostFormats
{
    protected static $instance;

    public static function get_instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->initHooks();
    }

    protected function initHooks()
    {
        add_action('after_setup_theme', array($this, 'init'));
        add_action('after_setup_theme', array($this, 'loadFormatFeatures'));
    }

    public function init()
    {
        add_theme_support('post-formats', apply_filters(
            'jankx_post_formats_allow_types',
            array(
                'aside',
                'gallery',
                'link',
                'image',
                'quote',
                'status',
                'video',
                'audio',
                'chat'
            )
        ));
    }

    public function loadFormatFeatures()
    {
        $post_formats     = get_theme_support('post-formats');
        $support_features = apply_filters('jankx_post_formats_format_features', array(
            'video' => VideoFormat::class,
        ));

        foreach ($support_features as $support_feature => $cls_feature) {
            if (in_array($support_feature, $post_formats)) {
                $feature = new $cls_feature();
            }
        }
    }
}
