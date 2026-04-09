<?php
/**
 * Main plugin class.
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

namespace Slug_Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Slug_Automator
 */
class Plugin {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'admin_notices', array( $this, 'show_no_connector_notice' ) );
		( new Auto_Slug( new Slugifier() ) )->register();
		( new Generate_Slug_Ability( new Slugifier() ) )->register();
	}

	/**
	 * Show an admin notice when no AI Connector is configured.
	 *
	 * @return void
	 */
	public function show_no_connector_notice(): void {
		if ( $this->has_ai_connector() ) {
			return;
		}

		wp_admin_notice(
			sprintf(
				/* translators: %s: URL to the Connectors settings page. */
				__( 'The Slug Automator plugin requires a valid AI Connector to function properly. Verify you have one or more AI Connectors configured <a href="%s">here</a>.', 'slug-automator' ),
				esc_url( admin_url( 'options-connectors.php' ) )
			),
			array( 'type' => 'error' )
		);
	}

	/**
	 * Check whether at least one AI provider connector with credentials is configured.
	 *
	 * @return bool
	 */
	public function has_ai_connector(): bool {
		foreach ( wp_get_connectors() as $connector ) {
			if ( 'ai_provider' !== $connector['type'] ) {
				continue;
			}

			$auth = $connector['authentication'];

			if ( 'api_key' === $auth['method'] && ! empty( $auth['setting_name'] ) && '' !== get_option( $auth['setting_name'], '' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		$asset_file = SLUG_AUTOMATOR_PLUGIN_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'slug-automator-editor',
			SLUG_AUTOMATOR_PLUGIN_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {}
}
