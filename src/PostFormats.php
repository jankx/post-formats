<?php
namespace Jankx\PostFormats;

use WP_Post;
use Jankx\PostFormats\Constracts\FormatConstract;
use Jankx\PostFormats\Format\VideoFormat;
use Jankx\PostFormats\Integrations\Manager;
use Jankx\Asset\AssetManager;

class PostFormats
{
    const VERSION = '1.0.0.40';

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
        add_action('init', array(Manager::class, 'loadIntegrations'), 20);

        if (wp_is_request('admin')) {
            add_action('admin_enqueue_scripts', array($this, 'registerAdminScripts'));
        }
    }

    public function init()
    {
        add_theme_support('post-formats', apply_filters(
            'jankx_post_formats_allow_types',
            array(
                'video'
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

                // Load format bootstrap if needed
                $feature->bootstrap();

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

    public static function getFeature($featureName)
    {
        if (isset(static::$features[$featureName])) {
            return static::$features[$featureName];
        }
    }

    public function get_suport_post_types()
    {
        $post_types = get_post_types();

        return array_filter(
            array_keys($post_types),
            function ($post_type) {
                return post_type_supports($post_type, 'post-formats');
            }
        );
    }

    public function registerPostFormatMetaDataBox()
    {
        add_meta_box(
            'jankx-post-format-metadata',
            __('Post Format Data', 'jankx_post_formats'),
            array($this, 'renderMetaDataBox'),
            $this->get_suport_post_types(),
            'side'
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
        if (isset($_POST['jankx_post_format'])) {
            $type = array_get($_POST['jankx_post_format'], 'type');
            if (!$type || !isset(static::$features[$type])) {
                return error_log(sprintf('Post format "%s" is invalid format', $type));
            }

            static::$features[$type]->save(
                $post_id,
                array_get($_POST['jankx_post_format'], $type, array())
            );
        }
    }

    protected function getPostFormatTemplates()
    {
        $post_formats = get_theme_support('post-formats');
        $templates = array();

        foreach (array_get($post_formats, 0) as $post_format) {
            if (!isset(static::$features[$post_format])) {
                $templates[$post_format] = false;
                continue;
            }
            $templates[$post_format] = static::$features[$post_format]->getMetaDataTemplate();
        }

        return $templates;
    }

    public function getPostFormatDefaultValues()
    {
        $post_formats = get_theme_support('post-formats');
        $defaultValues = array();

        foreach (array_get($post_formats, 0) as $post_format) {
            if (!isset(static::$features[$post_format])) {
                $defaultValues[$post_format] = new \stdClass();
                continue;
            }
            $defaultValues[$post_format] = static::$features[$post_format]->defaultValues();
        }

        return $defaultValues;
    }

    public function registerAdminScripts()
    {
        $current_screen = get_current_screen();
        if ('post' === $current_screen->base && post_type_supports($current_screen->id, 'post-formats')) {
            global $post;
            if (!is_a($post, WP_Post::class)) {
                return;
            }

            $current_format = get_post_format($post);
            $current_data = isset(static::$features[$current_format])
            ? (object) static::$features[$current_format]->prepareFormatData($post)
            : new \stdClass();

            wp_register_script('jankx-common', AssetManager::get_asset_url('public/js/common.js'), array(), static::VERSION, true);
            wp_register_script('tim', jankx_core_asset_url('libs/tim/tinytim.js'), array(), '1.0.0', true);
            wp_register_script(
                'jankx-post-formats',
                jankx_post_formats_asset_url('js/post-formats.js'),
                array('jankx-common', 'tim'),
                static::VERSION,
                true
            );

            wp_localize_script('jankx-post-formats', 'jankx_post_formats', apply_filters(
                'jankx_post_formats',
                array(
                    'ID' => $post->ID,
                    'current_format' => $current_format,
                    'is_block_editor' => method_exists($current_screen, 'is_block_editor')
                        ? $current_screen->is_block_editor()
                        : false,
                    'data' => $current_data,
                    'templates' => $this->getPostFormatTemplates(),
                    'default_values' => $this->getPostFormatDefaultValues()
                )
            ));
            wp_enqueue_script('jankx-post-formats');
        }
    }

    public function loadIntegrations()
    {
    }
}
