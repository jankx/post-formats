<?php
namespace Jankx\PostFormats;

use Jankx\PostFormats\Constracts\FormatConstract;
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
        add_action('init', array($this, 'init'));
        add_action('init', array($this, 'loadFormatFeatures'), 15);
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

        add_action('add_meta_boxes', array($this, 'registerPostFormatMetaDataBox'));
        add_action('save_post', array($this, 'savePostFormatMetaData'), 10, 2);
    }

    public function loadFormatFeatures()
    {
        $post_formats     = array_get(get_theme_support('post-formats'), 0);
        $support_features = apply_filters('jankx_post_formats_format_features', array(
            VideoFormat::FORMAT_NAME => VideoFormat::class,
        ));

        foreach ($support_features as $support_feature => $cls_feature) {
            if (in_array($support_feature, array_values($post_formats)) && class_exists($cls_feature)) {
                $feature = new $cls_feature();
                if (!is_a($feature, FormatConstract::class)) {
                    error_log(sprintf('Feature "%s" is skipped', $cls_feature));
                    continue;
                }
                $feature->loadFeature();
            } else {
                error_log(sprintf(
                    'Feature "%s" is not loaded for post "%s" format',
                    $cls_feature,
                    $support_feature
                ));
            }
        }
    }

    public function registerPostFormatMetaDataBox()
    {
        add_meta_box(
            'jankx-post-format-metadata',
            __('Post Format Data', 'jankx_post_formats'),
            array($this, 'renderMetaDataBox'),
            'post',
            'side',
            'high'
        );
    }

    public function renderMetaDataBox($post)
    {
        ?>
            <div id="jankx-post-formats"></div>
        <?php
    }

    public function savePostFormatMetaData($post_id, $post) {

    }
}
