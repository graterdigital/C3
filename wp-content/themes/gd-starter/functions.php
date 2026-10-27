<?php
/**
 * GD Starter theme — Grater Digital.
 */

defined( 'ABSPATH' ) || exit;

define( 'GD_THEME_VERSION', '1.2.2' );
define( 'GD_THEME_DIR', get_template_directory() );
define( 'GD_THEME_URI', get_template_directory_uri() );

require_once GD_THEME_DIR . '/inc/setup.php';
require_once GD_THEME_DIR . '/inc/assets.php';
require_once GD_THEME_DIR . '/inc/customizer.php';
require_once GD_THEME_DIR . '/inc/template-tags.php';
require_once GD_THEME_DIR . '/inc/patterns.php';
require_once GD_THEME_DIR . '/inc/contact-form.php';
require_once GD_THEME_DIR . '/inc/forms.php';
require_once GD_THEME_DIR . '/inc/seo.php';
