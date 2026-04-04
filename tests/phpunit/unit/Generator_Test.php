<?php

/**
 * Unit tests for the Generator class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Generator;

/**
 * Class Generator_Test
 */
class Generator_Test extends \WP_UnitTestCase {

	/**
	 * @var Generator&\PHPUnit\Framework\MockObject\MockObject
	 */
	private Generator $generator;

	public function set_up(): void {
		parent::set_up();

		$this->generator = $this->getMockBuilder( Generator::class )
			->onlyMethods( [ 'translate_with_wp_ai' ] )
			->getMock();
	}

	/**
	 * Test that generate() applies sanitize_title to the translated slug.
	 */
	public function test_generate_sanitizes_slug(): void {
		$this->generator->method( 'translate_with_wp_ai' )
			->willReturn( 'Hello World!' );

		$this->assertSame( 'hello-world', $this->generator->generate( 'any title' ) );
	}

	/**
	 * Test that generate() returns null when AI is unavailable.
	 */
	public function test_generate_returns_null_when_ai_unavailable(): void {
		$this->generator->method( 'translate_with_wp_ai' )
			->willReturn( null );

		$this->assertNull( $this->generator->generate( 'any title' ) );
	}

	/**
	 * Test that generate() returns null when sanitize_title produces an empty string.
	 */
	public function test_generate_returns_null_when_sanitized_slug_is_empty(): void {
		$this->generator->method( 'translate_with_wp_ai' )
			->willReturn( '!!!' );

		$this->assertNull( $this->generator->generate( 'any title' ) );
	}
}
