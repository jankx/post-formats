<?php
namespace Jankx\PostFormats\Format;

use Jankx\PostFormats\Abstracts\Format;

class VideoFormat extends Format
{
    const FORMAT_NAME = 'video';

    public function getName()
    {
        return static::FORMAT_NAME;
    }

    public function loadFeature()
    {
    }
}
