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
	 * post_name が空で publish のとき、生成された slug がセットされる。
	 */
	public function test_on_publish_sets_generated_slug(): void {
		$this->slugifier->method( 'generate' )->willReturn( 'my-post' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => '', 'post_title' => 'any title' ]
		);

		$this->assertSame( 'my-post', $result['post_name'] );
	}

	/**
	 * post_name が既に設定されているとき、変更しない。
	 */
	public function test_on_publish_does_not_overwrite_existing_slug(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => 'existing-slug', 'post_title' => 'any title' ]
		);

		$this->assertSame( 'existing-slug', $result['post_name'] );
	}

	/**
	 * status が publish でないとき、変更しない。
	 */
	public function test_on_publish_skips_non_publish_status(): void {
		$this->slugifier->expects( $this->never() )->method( 'generate' );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'draft', 'post_name' => '', 'post_title' => 'any title' ]
		);

		$this->assertSame( '', $result['post_name'] );
	}

	/**
	 * Slugifier が null を返すとき、post_name は空のまま。
	 */
	public function test_on_publish_keeps_empty_slug_when_generate_returns_null(): void {
		$this->slugifier->method( 'generate' )->willReturn( null );

		$result = $this->auto_slug->on_publish(
			[ 'post_status' => 'publish', 'post_name' => '', 'post_title' => 'any title' ]
		);

		$this->assertSame( '', $result['post_name'] );
	}
}
