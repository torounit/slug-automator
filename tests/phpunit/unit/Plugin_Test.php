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

	/**
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * Set up test.
	 */
	public function set_up(): void
	{
		parent::set_up();
		$this->plugin = new Plugin();
	}

	/**
	 * Test that init() registers expected hooks.
	 */
	public function test_init_registers_hooks(): void
	{
		$this->plugin->init();
		$this->assertNotFalse(has_action('enqueue_block_editor_assets', array($this->plugin, 'enqueue_block_editor_assets')));
	}
}
