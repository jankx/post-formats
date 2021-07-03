<?php
namespace Jankx\PostFormats\Abstracts;

use Jankx\PostFormats\Constracts\FormatConstract;

abstract class Format implements FormatConstract
{
    public function prepareFormatData() {
        return apply_filters();
    }
}
