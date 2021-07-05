<?php
namespace Jankx\PostFormats\Abstracts;

use Jankx\PostFormats\Constracts\FormatConstract;

abstract class Format implements FormatConstract
{
    protected static $cachedFormats = array();

    public function bootstrap()
    {
    }

    public static function getFormat($post)
    {
        $post = get_post($post->post);

        if (isset(self::$cachedFormats[$post->ID])) {
            return self::$cachedFormats[$post->ID];
        }
        return self::$cachedFormats[$post->ID] = get_post_format($post);
    }

    public function prepareFormatData($post)
    {
        return apply_filters(
            "jankx_prepare_{$this->getName()}_form_data",
            array(),
        );
    }

    public function defaultValues()
    {
        return array();
    }
}
