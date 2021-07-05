<?php
namespace Jankx\PostFormats\Constracts;

interface FormatConstract
{
    public function getName();

    public function getMetaDataTemplate();

    public function save($post_id, $data);
}
