<?php

/**
 * Unit tests for the Plugin class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Plugin;

/**
 * Class Plugin_Test
 */
class Plugin_Test extends \WP_UnitTestCase {

	private Plugin $plugin;

	public function set_up(): void {
		parent::set_up();
		$this->plugin = new Plugin();
	}

	/**
	 * Test that init() registers expected hooks.
	 */
	public function test_init_registers_hooks(): void {
		$this->plugin->init();

		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->plugin, 'enqueue_block_editor_assets' ) ) );
		$this->assertNotFalse( has_action( 'admin_notices', array( $this->plugin, 'show_no_connector_notice' ) ) );
	}

	/**
	 * Test that has_ai_connector() returns false when no credentials are set.
	 */
	public function test_has_ai_connector_returns_false_when_no_credentials(): void {
		$this->assertFalse( $this->plugin->has_ai_connector() );
	}

	/**
	 * Test that has_ai_connector() returns true when credentials are set.
	 */
	public function test_has_ai_connector_returns_true_when_credentials_set(): void {
		update_option( 'connectors_ai_openai_api_key', 'test-api-key' );

		$this->assertTrue( $this->plugin->has_ai_connector() );
	}

	/**
	 * Test that notice is output when no connector is configured.
	 */
	public function test_show_no_connector_notice_outputs_notice_when_no_connector(): void {
		ob_start();
		$this->plugin->show_no_connector_notice();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'Slug Automator', $output );
		$this->assertStringContainsString( 'options-connectors.php', $output );
	}

	/**
	 * Test that no notice is output when a connector is configured.
	 */
	public function test_show_no_connector_notice_is_silent_when_connector_exists(): void {
		update_option( 'connectors_ai_openai_api_key', 'test-api-key' );

		ob_start();
		$this->plugin->show_no_connector_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}
}
