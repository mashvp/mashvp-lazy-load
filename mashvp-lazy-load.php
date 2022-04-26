<?php

/**
 * Plugin Name: Mashvp — Lazy Load
 * Plugin URI: http://mashvp.com
 * Description: Lazy load images with BlurHash
 * Version: 0.1.2
 * Author: Mashvp
 * Author URI: http://mashvp.com
 * Text Domain: mashvp-lazy-load
 * Domain Path: /languages
 */

defined('ABSPATH') or die();

if (defined('MASHVP_LAZY_LOAD')) {
    error_log('[mashvp-lazy-load] Plugin loaded twice, aborting.');
    return false;
}

define('MASHVP_LAZY_LOAD', true);
define('MASHVP_LAZY_LOAD__VERSION', '0.1.2');
define('MASHVP_LAZY_LOAD__DIR', basename(dirname(__FILE__)));
define('MASHVP_LAZY_LOAD__PATH', plugin_dir_path(__FILE__));

include __DIR__ . '/includes/register.php';
