<?php
/**
 * Generate Slug Ability Class
 *
 * Registers the slug generation ability with the WordPress Abilities API.
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

namespace Slug_Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Generate_Slug_Ability
 */
class Generate_Slug_Ability {

	/**
	 * Constructor.
	 *
	 * @param Slugifier $slugifier Slugifier instance.
	 */
	public function __construct( private readonly Slugifier $slugifier ) {
	}

	/**
	 * Register ability category and ability via WordPress Abilities API hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_ability' ) );
	}

	/**
	 * Register the slug-automator ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		wp_register_ability_category(
			'slug-automator',
			array(
				'label'       => __( 'Slug Automator', 'slug-automator' ),
				'description' => __( 'Abilities provided by the Slug Automator plugin.', 'slug-automator' ),
			)
		);
	}

	/**
	 * Register the generate-slug ability.
	 *
	 * @return void
	 */
	public function register_ability(): void {
		wp_register_ability(
			'slug-automator/generate-slug',
			array(
				'label'               => __( 'Generate Slug', 'slug-automator' ),
				'description'         => __( 'Generates an English URL slug from a post title using AI.', 'slug-automator' ),
				'category'            => 'slug-automator',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'title'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => __( 'The post title to generate a slug from.', 'slug-automator' ),
						),
						'context' => array(
							'type'        => 'object',
							'description' => __( 'The target object this slug is being generated for.', 'slug-automator' ),
							'properties'  => array(
								'type' => array(
									'type'        => 'string',
									'enum'        => array( 'post' ),
									'description' => __( 'The object type. Currently only "post" is supported.', 'slug-automator' ),
								),
								'id'   => array(
									'type'        => 'integer',
									'description' => __( 'The ID of the target object.', 'slug-automator' ),
								),
							),
							'required'    => array( 'type', 'id' ),
						),
						'avoid'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_title',
							'description'       => __( 'Optional. An existing slug that the generated slug must differ from.', 'slug-automator' ),
						),
					),
					'required'   => array( 'title', 'context' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'slug' => array(
							'type'        => 'string',
							'description' => __( 'The generated URL slug.', 'slug-automator' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_callback' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'meta'                => array( 'show_in_rest' => true ),
			)
		);
	}

	/**
	 * Execute the slug generation ability.
	 *
	 * @param array $input Input data containing 'title'.
	 *
	 * @return array|\WP_Error
	 */
	public function execute_callback( array $input ): array|\WP_Error {
		$slug = $this->slugifier->generate( $input['title'], $input['avoid'] ?? null );

		if ( null === $slug ) {
			return new \WP_Error(
				'slug_automator_generate_failed',
				__( 'Failed to generate slug. The AI service may be unavailable.', 'slug-automator' )
			);
		}

		return array( 'slug' => $slug );
	}

	/**
	 * Check whether the current user can execute this ability.
	 *
	 * Dispatches to a type-specific permission check based on context['type'].
	 * Currently only 'post' is supported.
	 *
	 * @param array $input Input data containing 'context' with 'type' and 'id'.
	 *
	 * @return bool|\WP_Error
	 */
	public function permission_callback( array $input ): bool|\WP_Error {
		$context = is_array( $input['context'] ?? null ) ? $input['context'] : array();
		$type    = isset( $context['type'] ) ? sanitize_key( $context['type'] ) : '';
		$id      = isset( $context['id'] ) ? absint( $context['id'] ) : 0;

		if ( '' === $type || 0 === $id ) {
			return new \WP_Error(
				'invalid_context',
				__( 'A valid context (type and id) is required.', 'slug-automator' )
			);
		}

		if ( 'post' === $type ) {
			return $this->check_post_permission( $id );
		}

		return new \WP_Error(
			'unsupported_context_type',
			/* translators: %s: context type. */
			sprintf( __( 'Unsupported context type: %s.', 'slug-automator' ), $type )
		);
	}

	/**
	 * Check whether the current user can generate a slug for a specific post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool|\WP_Error
	 */
	private function check_post_permission( int $post_id ): bool|\WP_Error {
		if ( ! get_post( $post_id ) ) {
			return new \WP_Error(
				'post_not_found',
				/* translators: %d: Post ID. */
				sprintf( __( 'Post with ID %d not found.', 'slug-automator' ), $post_id )
			);
		}

		if ( current_user_can( 'edit_post', $post_id ) ) {
			return true;
		}

		return new \WP_Error(
			'insufficient_capabilities',
			__( 'You do not have permission to generate a slug for this post.', 'slug-automator' )
		);
	}
}
