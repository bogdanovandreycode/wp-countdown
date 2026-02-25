<?php
/*
Plugin Name: wp-countdown
Plugin URI: https://github.com/bogdanovandreycode
Description: Плагин обратного отсчёта с поддержкой WPBakery Page Builder. Создавайте красивые динамические таймеры обратного отсчёта с гибкими настройками отображаемых единиц времени.
Version: 1.0
Author: Arraydev
Author URI: https://github.com/bogdanovandreycode
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-countdown
Domain Path: /languages
*/

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

use CountdownPlugin\Boot;

new Boot(__FILE__, __DIR__);
