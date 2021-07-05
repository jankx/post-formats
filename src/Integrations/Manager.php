<?php
namespace Jankx\PostFormats\Integrations;

class Manager
{
    public static function loadIntegrations()
    {
        new PostLayoutIntegration;
    }
}
