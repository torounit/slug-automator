<?php

/**
 * Unit tests for Slug_Automator class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Slug_Automator;

/**
 * Class Slug_Automator_Test
 */
class Slug_Automator_Test extends \WP_UnitTestCase
{

	/**
	 * @var Slug_Automator
	 */
	private Slug_Automator $plugin;

	/**
	 * Set up test.
	 */
	public function set_up(): void
	{
		parent::set_up();
		$this->plugin = new Slug_Automator();
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
