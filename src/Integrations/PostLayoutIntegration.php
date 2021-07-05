<?php
namespace Jankx\PostFormats\Integrations;

use Jankx\PostFormats\Abstracts\Format;
use Jankx\PostFormats\PostFormats;

class PostLayoutIntegration
{
    public function __construct()
    {
        add_action('jankx_post_layout_before_loop_post_thumbnail', array($this, 'loadPostFormatFeatures'));
    }

    public function loadPostFormatFeatures($post)
    {
        $format = Format::getFormat($post);
        $feature = PostFormats::getFeature($format);
        if (!$feature) {
            return;
        }

        switch ($format) {
            case 'video':
                return $feature->makeVideoOverlay($post);
        }
    }
}
