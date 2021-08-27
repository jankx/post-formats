<?php
namespace Jankx\PostFormats\Format;

use Jankx;
use Jankx\PostFormats\Abstracts\Format;
use Jankx\TemplateLoader;

class VideoFormat extends Format
{
    const FORMAT_NAME = 'video';

    public function getName()
    {
        return static::FORMAT_NAME;
    }

    public function makeVideoOverlay($post, $data_index = null)
    {
        $jankx_post_format = get_post_meta($post->ID, 'jankx_post_format', true);
        $video_data = array_get($jankx_post_format, 'video');
        $video_url = array_get($video_data, 'url');
        $engine = TemplateLoader::getTemplateEngine();
        if (empty($video_url) || !$engine) {
            return;
        }

        $attributes = array(
            'class' => array('overlay', 'video-overlay', 'has-lightbox'),
            'data-src' => $video_url,
        );
        if (!is_null($data_index)) {
            $attributes['data-gallery-index'] = intval($data_index);
        }

        echo sprintf(
            '<div %s>',
            jankx_generate_html_attributes(apply_filters(
                'jankx/layout/post/format/video/overlay/attributes',
                $attributes
            ))
        );
        $engine->render('video-overlay');
        echo '</div>';
    }

    public function prepareFormatData($post)
    {
        $metadata = get_post_meta($post->ID, 'jankx_post_format', true);

        return apply_filters(
            "jankx_prepare_{$this->getName()}_form_data",
            array_get(
                $metadata,
                $this->getName(),
                array()
            )
        );
    }

    public function getMetaDataTemplate()
    {
        ob_start();
        ?>
        <div id="video-format-metadata">
            <input type="hidden" name="jankx_post_format[type]" value="<?php echo $this->getName(); ?>">
            <p>
                <label for="jankx_post_format_video_url"><?php echo esc_html(__('Video URL', 'jankx_post_formats')); ?>:</label>
                <input
                    type="text"
                    id="jankx_post_format_video_url"
                    class="widefat"
                    name="jankx_post_format[<?php echo $this->getName(); ?>][url]"
                    value="{{ url }}"
                />
            </p>
        </div>
        <?php

        return ob_get_clean();
    }

    public function defaultValues()
    {
        return array(
            'url' => '',
        );
    }

    public function save($post_id, $data)
    {
        update_post_meta($post_id, 'jankx_post_format', array(
            $this->getName() => $data,
        ));
    }
}
