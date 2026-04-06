<?php

/**
 * Unit tests for the Auto_Slug class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Auto_Slug;
use Slug_Automator\Slugifier;

/**
 * Class Auto_Slug_Test
 */
class Auto_Slug_Test extends \WP_UnitTestCase {

	/**
	 * @var Slugifier&\PHPUnit\Framework\MockObject\MockObject
	 */
	private Slugifier $slugifier;

	private Auto_Slug $auto_slug;

	public function set_up(): void {
		parent::set_up();

		$this->slugifier = $this->getMockBuilder( Slugifier::class )
			->onlyMethods( [ 'generate' ] )
			->getMock();

		$this->auto_slug = new Auto_Slug( $this->slugifier );
	}

	/**
	 * When post_name is empty on publish, the generated slug is set.
	 * $data['post_name'] already contains the WordPress-generated value from the title,
	 * but $postarr['post_name'] is empty so AI slug generation runs.
	 */
	public function test_on_publish_sets_generated_slug(): void {
		$this->slugifier->method( 'generate' )->willReturn( 'my-post' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => 'any-title', 'post_title' => 'any title' ],
			[ 'post_name' => '' ]
		);

		$this->assertSame( 'my-post', $result['post_name'] );
	}

	/**
	 * When post_name is already set, it is not overwritten.
	 */
	public function test_on_publish_does_not_overwrite_existing_slug(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => 'existing-slug', 'post_title' => 'any title' ],
			[ 'post_name' => 'existing-slug' ]
		);

		$this->assertSame( 'existing-slug', $result['post_name'] );
	}

	/**
	 * When post status is not publish, nothing is changed.
	 */
	public function test_on_publish_skips_non_publish_status(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'draft', 'post_name' => '', 'post_title' => 'any title' ],
			[ 'post_name' => '' ]
		);

		$this->assertSame( '', $result['post_name'] );
	}

	/**
	 * When Slugifier returns null, post_name is left unchanged.
	 */
	public function test_on_publish_keeps_empty_slug_when_generate_returns_null(): void {
		$this->slugifier->method( 'generate' )->willReturn( null );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => 'any-title', 'post_title' => 'any title' ],
			[ 'post_name' => '' ]
		);

		$this->assertSame( 'any-title', $result['post_name'] );
	}
}
