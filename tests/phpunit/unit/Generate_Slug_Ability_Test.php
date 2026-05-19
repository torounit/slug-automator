<?php

/**
 * Unit tests for the Generate_Slug_Ability class.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Unit;

use Slug_Automator\Generate_Slug_Ability;
use Slug_Automator\Slugifier;

/**
 * Class Generate_Slug_Ability_Test
 */
class Generate_Slug_Ability_Test extends \WP_UnitTestCase {

	/**
	 * @var Slugifier&\PHPUnit\Framework\MockObject\MockObject
	 */
	private Slugifier $slugifier;

	private Generate_Slug_Ability $ability;

	public function set_up(): void {
		parent::set_up();

		$this->slugifier = $this->getMockBuilder( Slugifier::class )
			->onlyMethods( array( 'generate' ) )
			->getMock();

		$this->ability = new Generate_Slug_Ability( $this->slugifier );
	}

	// --- execute() ---

	/**
	 * execute() returns slug array when Slugifier succeeds.
	 */
	public function test_execute_returns_slug_on_success(): void {
		$this->slugifier->method( 'generate' )->willReturn( 'my-post' );

		$result = $this->ability->execute_callback( array( 'title' => '私のポスト' ) );

		$this->assertSame( array( 'slug' => 'my-post' ), $result );
	}

	/**
	 * execute() passes title to Slugifier::generate().
	 */
	public function test_execute_passes_title_to_slugifier(): void {
		$this->slugifier
			->expects( $this->once() )
			->method( 'generate' )
			->with( 'Hello World', null )
			->willReturn( 'hello-world' );

		$this->ability->execute_callback( array( 'title' => 'Hello World' ) );
	}

	/**
	 * execute() passes avoid to Slugifier::generate() when provided.
	 */
	public function test_execute_passes_avoid_to_slugifier(): void {
		$this->slugifier
			->expects( $this->once() )
			->method( 'generate' )
			->with( 'Hello World', 'old-slug' )
			->willReturn( 'hello-world-2' );

		$this->ability->execute_callback( array( 'title' => 'Hello World', 'avoid' => 'old-slug' ) );
	}

	/**
	 * execute() returns WP_Error when Slugifier returns null.
	 */
	public function test_execute_returns_wp_error_when_generate_returns_null(): void {
		$this->slugifier->method( 'generate' )->willReturn( null );

		$result = $this->ability->execute_callback( array( 'title' => 'any title' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'slug_automator_generate_failed', $result->get_error_code() );
	}

	// --- permission_callback() — invalid context ---

	/**
	 * permission_callback() returns WP_Error when context is missing.
	 */
	public function test_check_permission_returns_wp_error_when_context_missing(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$result = $this->ability->permission_callback( array( 'title' => 'test' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_context', $result->get_error_code() );
	}

	/**
	 * permission_callback() returns WP_Error when context is missing type.
	 */
	public function test_check_permission_returns_wp_error_when_context_type_missing(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array( 'id' => 1 ),
			)
		);

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_context', $result->get_error_code() );
	}

	/**
	 * permission_callback() returns WP_Error when context is missing id.
	 */
	public function test_check_permission_returns_wp_error_when_context_id_missing(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array( 'type' => 'post' ),
			)
		);

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'invalid_context', $result->get_error_code() );
	}

	/**
	 * permission_callback() returns WP_Error for unsupported context type.
	 */
	public function test_check_permission_returns_wp_error_for_unsupported_context_type(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array(
					'type' => 'term',
					'id'   => 1,
				),
			)
		);

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'unsupported_context_type', $result->get_error_code() );
	}

	// --- permission_callback() — post context ---

	/**
	 * permission_callback() returns true when user can edit the given post.
	 */
	public function test_check_permission_with_post_id_returns_true_for_owner(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array(
					'type' => 'post',
					'id'   => $post_id,
				),
			)
		);

		$this->assertTrue( $result );
	}

	/**
	 * permission_callback() returns WP_Error when post does not exist.
	 */
	public function test_check_permission_with_nonexistent_post_id_returns_wp_error(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array(
					'type' => 'post',
					'id'   => 999999,
				),
			)
		);

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'post_not_found', $result->get_error_code() );
	}

	/**
	 * permission_callback() returns WP_Error when user cannot edit the post.
	 */
	public function test_check_permission_with_post_id_returns_wp_error_for_other_user(): void {
		$author_id = self::factory()->user->create( array( 'role' => 'author' ) );
		$other_id  = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $other_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $author_id ) );

		$result = $this->ability->permission_callback(
			array(
				'title'   => 'test',
				'context' => array(
					'type' => 'post',
					'id'   => $post_id,
				),
			)
		);

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'insufficient_capabilities', $result->get_error_code() );
	}

	// --- register() ---

	/**
	 * register() adds actions when Abilities API is available.
	 */
	public function test_register_adds_hooks_when_api_available(): void {
		if ( ! function_exists( 'wp_register_ability_category' ) || ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'Abilities API not available.' );
		}

		$this->ability->register();

		$this->assertNotFalse(
			has_action( 'wp_abilities_api_categories_init', array( $this->ability, 'register_category' ) )
		);
		$this->assertNotFalse(
			has_action( 'wp_abilities_api_init', array( $this->ability, 'register_ability' ) )
		);
	}
}
