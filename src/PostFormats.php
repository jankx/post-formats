<?php
namespace Jankx\PostFormats;

use WP_Post;
use Jankx\PostFormats\Constracts\FormatConstract;
use Jankx\PostFormats\Format\VideoFormat;

class PostFormats
{
    const VERSION = '1.0.0.17';

    protected static $instance;
    protected static $features;

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
        if (wp_is_request('admin')) {
            add_action('admin_enqueue_scripts', array($this, 'registerAdminScripts'), 50);
        }
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

                static::$features[$feature->getName()] = $feature;
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

    public function savePostFormatMetaData($post_id, $post)
    {
    }

    public function registerAdminScripts()
    {
        $current_screen = get_current_screen();
        if ('post' === $current_screen->id) {
            global $post;
            if (!is_a($post, WP_Post::class)) {
                return;
            }

            $current_format = get_post_format($post);


            wp_register_script(
                'jankx-post-formats',
                jankx_post_formats_asset_url('js/post-formats.js'),
                array(),
                static::VERSION,
                true
            );

            wp_localize_script('jankx-post-formats', 'jankx_post_formats', apply_filters(
                'jankx_post_formats',
                array(
                    'ID' => $post->ID,
                    'current_format' => $current_format,
                    'gutenberg_active' => method_exists($current_screen, 'is_block_editor')
                        ? $current_screen->is_block_editor()
                        : false,
                    'data' => isset(static::$features[$current_format])
                        ? static::$features[$current_format]->prepareFormatData()
                        : array(),
                )
            ));
            wp_enqueue_script('jankx-post-formats');
        }
    }
}
