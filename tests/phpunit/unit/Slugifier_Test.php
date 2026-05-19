<?php

/**
 * Unit tests for the Slugifier class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Slugifier;

/**
 * Class Slugifier_Test
 */
class Slugifier_Test extends \WP_UnitTestCase {

	/**
	 * @var Slugifier&\PHPUnit\Framework\MockObject\MockObject
	 */
	private Slugifier $slugifier;

	public function set_up(): void {
		parent::set_up();

		$this->slugifier = $this->getMockBuilder( Slugifier::class )
			->onlyMethods( [ 'translate_with_wp_ai' ] )
			->getMock();
	}

	/**
	 * Test that generate() applies sanitize_title to the translated slug.
	 */
	public function test_generate_sanitizes_slug(): void {
		$this->slugifier->method( 'translate_with_wp_ai' )
			->willReturn( 'Hello World!' );

		$this->assertSame( 'hello-world', $this->slugifier->generate( 'any title' ) );
	}

	/**
	 * Test that generate() returns null when AI is unavailable.
	 */
	public function test_generate_returns_null_when_ai_unavailable(): void {
		$this->slugifier->method( 'translate_with_wp_ai' )
			->willReturn( null );

		$this->assertNull( $this->slugifier->generate( 'any title' ) );
	}

	/**
	 * Test that generate() returns null when sanitize_title produces an empty string.
	 */
	public function test_generate_returns_null_when_sanitized_slug_is_empty(): void {
		$this->slugifier->method( 'translate_with_wp_ai' )
			->willReturn( '!!!' );

		$this->assertNull( $this->slugifier->generate( 'any title' ) );
	}

	/**
	 * generate() passes sanitize_title($avoid) to translate_with_wp_ai when avoid contains special chars.
	 */
	public function test_generate_passes_sanitized_avoid_to_translator(): void {
		$this->slugifier
			->expects( $this->once() )
			->method( 'translate_with_wp_ai' )
			->with( 'any title', 'hello-world' )
			->willReturn( 'different-slug' );

		$this->slugifier->generate( 'any title', 'Hello World!' );
	}

	/**
	 * generate() passes null to translate_with_wp_ai when avoid is an empty string.
	 */
	public function test_generate_passes_null_to_translator_when_avoid_is_empty(): void {
		$this->slugifier
			->expects( $this->once() )
			->method( 'translate_with_wp_ai' )
			->with( 'any title', null )
			->willReturn( 'my-slug' );

		$this->slugifier->generate( 'any title', '' );
	}
}
