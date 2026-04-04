<?php
/**
 * Plugin Name:       Slug Automator
 * Plugin URI:        https://github.com/torounit/slug-automator
 * Description:       Automates slug generation for WordPress posts.
 * Version:           0.1.0
 * Requires at least: 7.0
 * Requires PHP:      8.2
 * Author:            Toro_Unit
 * Author URI:        https://torounit.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slug-automator
 * Domain Path:       /languages
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SLUG_AUTOMATOR_VERSION', '0.1.0' );
define( 'SLUG_AUTOMATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLUG_AUTOMATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SLUG_AUTOMATOR_PLUGIN_FILE', __FILE__ );

require_once SLUG_AUTOMATOR_PLUGIN_DIR . 'includes/class-slugifier.php';
require_once SLUG_AUTOMATOR_PLUGIN_DIR . 'includes/class-auto-slug.php';
require_once SLUG_AUTOMATOR_PLUGIN_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Slug_Automator\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Slug_Automator\\Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	function (): void {
		( new Slug_Automator\Plugin() )->init();
	}
);
