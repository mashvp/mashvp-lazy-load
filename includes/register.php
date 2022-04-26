<?php

require __DIR__ . '/../vendor/autoload.php';
require 'autoloader.php';

use Mashvp\LazyLoad\Utils;
use kornrunner\Blurhash\Blurhash;

if (!function_exists('mvplzl__array_safe_get')) {
    function mvplzl__array_safe_get($array, ...$keys)
    {
        $current = $array;

        foreach ($keys as $key) {
            if (isset($current) && isset($current[$key])) {
                $current = $current[$key];
            } else {
                return null;
            }
        }

        return $current;
    }
}

if (!function_exists('mvplzl__attachment_alt')) {
    function mvplzl__attachment_alt($attachment_id)
    {
        $thumb_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

        if (empty($thumb_alt)) {
            $attachment = get_post($attachment_id);
        }

        if (empty($thumb_alt)) {
            $thumb_alt = $attachment->post_excerpt;
        }

        if (empty($thumb_alt)) {
            $thumb_alt = $attachment->post_title;
        }

        return esc_attr(trim(strip_tags($thumb_alt)));
    }
}

add_action('init', 'mvplzl__init', 5);
function mvplzl__init()
{
    if (!is_admin()) {
        $css         = Utils::dist_uri('front.css');
        $css_version = @filemtime(Utils::dist_path('front.css'));

        $js         = Utils::dist_uri('front.min.js');
        $js_version = @filemtime(Utils::dist_path('front.min.js'));

        wp_register_style('mashvp-lazy-load--front-styles', $css, [], $css_version);
        wp_register_script('mashvp-lazy-load--front-script', $js, [], $js_version);
    }
}

add_action('wp_enqueue_scripts', 'mvplzl__wp_enqueue_scripts', 5);
function mvplzl__wp_enqueue_scripts()
{
    if (!is_admin()) {
        $css         = Utils::dist_uri('front.css');
        $css_version = @filemtime(Utils::dist_path('front.css'));

        $js         = Utils::dist_uri('front.min.js');
        $js_version = @filemtime(Utils::dist_path('front.min.js'));

        wp_enqueue_style('mashvp-lazy-load--front-styles', $css, [], $css_version);
        wp_enqueue_script('mashvp-lazy-load--front-script', $js, [], $js_version);
    }
}

add_action('wp_generate_attachment_metadata', 'mvplzl__generate_attachment_metadata');
function mvplzl__generate_attachment_metadata($metadata)
{
    $mime_type = mvplzl__array_safe_get($metadata, 'sizes', 'thumbnail', 'mime-type');

    // Skip unsupported MIME types
    if (
        !in_array(
            $mime_type,
            [
                'image/png',
                'image/jpeg', 'image/pjpeg',
                'image/gif',
                'image/x-icon',
                'image/bmp',
                'image/tiff',
                'image/webp'
            ]
        )
    ) {
        return $metadata;
    }

    $uploads_dir = mvplzl__array_safe_get(wp_upload_dir(), 'path');
    $filename    = $uploads_dir . '/' . mvplzl__array_safe_get($metadata, 'sizes', 'thumbnail', 'file');

    $components_x = 4;
    $components_y = 3;

    $metadata['blurhash'] = [
        'x'    => $components_x,
        'y'    => $components_y,
        'hash' => null
    ];

    if (is_file($filename) && is_readable($filename)) {
        try {
            $image  = imagecreatefromstring(file_get_contents($filename));
            $width  = mvplzl__array_safe_get($metadata, 'sizes', 'thumbnail', 'width');
            $height = mvplzl__array_safe_get($metadata, 'sizes', 'thumbnail', 'height');

            $pixels = [];
            for ($y = 0; $y < $height; $y += 1) {
                $row = [];

                for ($x = 0; $x < $width; $x += 1) {
                    $index = imagecolorat($image, $x, $y);
                    $colors = imagecolorsforindex($image, $index);

                    $row[] = [
                        mvplzl__array_safe_get($colors, 'red'),
                        mvplzl__array_safe_get($colors, 'green'),
                        mvplzl__array_safe_get($colors, 'blue'),
                    ];
                }

                $pixels[] = $row;
            }

            $blurhash = Blurhash::encode($pixels, $components_x, $components_y);

            $metadata['blurhash'] = [
                'x'    => $components_x,
                'y'    => $components_y,
                'hash' => $blurhash
            ];
        } catch (Exception $err) {
            error_log("[mashvp-lazy-load] Error encoding BlurHash: $err");
        }
    }

    return $metadata;
}

add_filter('attachment_fields_to_edit', 'mvplzl__attachment_fields_to_edit', null, 2);
function mvplzl__attachment_fields_to_edit($form_fields, $post)
{
    $meta     = wp_get_attachment_metadata($post->ID);
    $blurhash = mvplzl__array_safe_get($meta, 'blurhash');

    if ($blurhash) {
        $hash = esc_attr($blurhash['hash']);

        $form_fields['blurhash'] = [
            'label' => 'BlurHash',
            'input' => 'html',
            'html'  => "<input type='text' class='text urlfield' readonly='readonly' value='$hash' /><br />",
        ];
    }

    return $form_fields;
}

if (!function_exists('mvplzl__blurhash_image')) {
    function mvplzl__blurhash_image($attachment_id, $args)
    {
        $args = wp_parse_args($args, [
            'size'            => 'full',
            'format'          => 'auto',
            'html_attributes' => [],
        ]);

        $size   = mvplzl__array_safe_get($args, 'size');
        $format = mvplzl__array_safe_get($args, 'format');

        $html_attrs = mvplzl__array_safe_get($args, 'html_attributes');
        $additional_classes = '';

        if (isset($html_attrs['class'])) {
            $additional_classes = $html_attrs['class'];
            unset($html_attrs['class']);
        }

        $html_attrs = implode(
            ' ',
            array_map(function ($key, $value) {
                $key   = esc_attr($key);
                $value = esc_attr($value);

                return "$key=\"$value\"";
            }, array_keys($html_attrs), $html_attrs)
        );

        if (get_post_type($attachment_id) === 'attachment') {
            $src_full            = wp_get_attachment_image_src($attachment_id, 'full');
            $url_full            = esc_attr($src_full[0]);
            $width_full          = esc_attr($src_full[1]);
            $height_full         = esc_attr($src_full[2]);

            $src            = wp_get_attachment_image_src($attachment_id, $size);
            $url            = esc_attr($src[0]);
            $width          = esc_attr($src[1]);
            $height         = esc_attr($src[2]);
            $height_percent = 100;

            if (is_numeric($height) && is_numeric($width) && $width !== 0) {
                $height_percent = $height / $width * 100;
            }

            $alt      = esc_attr(mvplzl__attachment_alt($attachment_id));
            $caption  = esc_attr(wp_get_attachment_caption($attachment_id));
            $meta     = wp_get_attachment_metadata($attachment_id);
            $blurhash = mvplzl__array_safe_get($meta, 'blurhash');

            if (is_admin()) {
                $classes = esc_attr(
                    implode(' ', array_filter(
                        [
                            'image-wrapper',
                            "format-{$format}",
                            'mvplzl',
                            'mvplzl__image',
                            'mvplzl__image--admin',
                            $additional_classes
                        ]
                    ))
                );

                echo <<<HTML
                    <figure
                        class="$classes"
                        style="--aspect-ratio: $width / $height; --height-percent: {$height_percent}%"
                    >
                        <img src="$url" alt="$alt">
                    </figure>
                HTML;

                return null;
            }

            if ($blurhash && mvplzl__array_safe_get($blurhash, 'hash')) {
                $components_x = esc_attr(mvplzl__array_safe_get($blurhash, 'x'));
                $components_y = esc_attr(mvplzl__array_safe_get($blurhash, 'y'));
                $hash         = esc_attr(mvplzl__array_safe_get($blurhash, 'hash'));

                $classes = esc_attr(
                    implode(' ', array_filter(
                        [
                            'image-wrapper',
                            "format-{$format}",
                            'mvplzl',
                            'mvplzl__image',
                            'mvplzl__image--blurhash',
                            $additional_classes
                        ]
                    ))
                );

                echo <<<HTML
                    <figure
                        class="$classes"
                        data-controller="mvplzl--lazy-load"
                        data-mvplzl--lazy-load-components-x-value="$components_x"
                        data-mvplzl--lazy-load-components-y-value="$components_y"
                        data-mvplzl--lazy-load-blurhash-value="$hash"
                        data-mvplzl--lazy-load-src-value="$url"
                        data-mvplzl--lazy-load-image-width-value="$width"
                        data-mvplzl--lazy-load-image-height-value="$height"
                        data-mvplzl--lazy-load-src-full-value="$url_full"
                        data-mvplzl--lazy-load-image-width-full-value="$width_full"
                        data-mvplzl--lazy-load-image-height-full-value="$height_full"
                        data-mvplzl--lazy-load-alt-value="$alt"
                        data-mvplzl--lazy-load-caption-value="$caption"
                        style="--aspect-ratio: $width / $height; --height-percent: {$height_percent}%"
                        $html_attrs
                    >
                        <canvas width="32" height="32" data-mvplzl--lazy-load-target="canvas"></canvas>
                        <img src="" alt="$alt" data-mvplzl--lazy-load-target="image" data-action="load->mvplzl--lazy-load#loaded">

                        <figcaption>$caption</figcaption>
                    </figure>
                HTML;
            } else {
                $classes = esc_attr(
                    implode(' ', array_filter(
                        [
                            'image-wrapper',
                            "format-{$format}",
                            'mvplzl',
                            'mvplzl__image',
                            'mvplzl__image--no-blurhash',
                            $additional_classes
                        ]
                    ))
                );

                echo <<<HTML
                    <figure
                        class="$classes"
                        style="--aspect-ratio: $width / $height; --height-percent: {$height_percent}%"
                        $html_attrs
                    >
                        <img src="$url" alt="$alt">
                    </figure>
                HTML;
            }
        }
    }
}
