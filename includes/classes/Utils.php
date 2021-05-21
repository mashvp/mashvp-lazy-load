<?php

namespace Mashvp\LazyLoad;

abstract class Utils
{
    public const PLUGIN_BASE_PATH = WP_PLUGIN_DIR . '/mashvp-lazy-load';

    public static function asset_uri($name)
    {
        $asset_dir = plugin_dir_url('mashvp-lazy-load') . 'mashvp-lazy-load/assets';
        $file_path = $asset_dir . "/$name";

        return $file_path;
    }

    public static function asset_path($name)
    {
        $asset_dir = self::PLUGIN_BASE_PATH . '/assets';
        $file_path = $asset_dir . "/$name";

        return $file_path;
    }

    public static function dist_uri($name)
    {
        $dist_dir = plugin_dir_url('mashvp-lazy-load') . 'mashvp-lazy-load/dist';
        $file_path = $dist_dir . "/$name";

        return $file_path;
    }

    public static function dist_path($name)
    {
        $dist_dir = self::PLUGIN_BASE_PATH . '/dist';
        $file_path = $dist_dir . "/$name";

        return $file_path;
    }

    public static function template_path($name)
    {
        $template_dir = self::PLUGIN_BASE_PATH . '/templates';
        $file_path = $template_dir . "/$name.html.php";

        return $file_path;
    }

    public static function get($array, $prop, $default = null)
    {
        if ($array && is_array($array) && isset($array[$prop])) {
            return $array[$prop];
        }

        return $default;
    }
}
