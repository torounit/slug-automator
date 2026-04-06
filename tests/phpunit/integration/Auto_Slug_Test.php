<?php

/**
 * Integration tests for the Auto_Slug class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Integration;

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
		$this->auto_slug->register();
	}

	/**
	 * The slug is generated when a post is published without an explicit slug.
	 */
	public function test_slug_is_generated_on_post_publish(): void {
		$this->slugifier->method( 'generate' )->willReturn( 'my-post' );

		$post_id = $this->factory()->post->create(
			[
				'post_title'  => 'any title',
				'post_status' => 'publish',
				'post_name'   => '',
			]
		);

		$this->assertSame( 'my-post', get_post( $post_id )->post_name );
	}

	/**
	 * An explicitly set slug is not overwritten.
	 */
	public function test_existing_slug_is_not_overwritten(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$post_id = $this->factory()->post->create(
			[
				'post_title'  => 'any title',
				'post_status' => 'publish',
				'post_name'   => 'existing-slug',
			]
		);

		$this->assertSame( 'existing-slug', get_post( $post_id )->post_name );
	}

	/**
	 * No slug is generated for draft posts.
	 */
	public function test_slug_is_not_generated_for_draft(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$post_id = $this->factory()->post->create(
			[
				'post_title'  => 'any title',
				'post_status' => 'draft',
			]
		);

		$this->assertSame( '', get_post( $post_id )->post_name );
	}
}
