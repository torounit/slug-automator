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
	public function __construct( private readonly Slugifier $slugifier ) {}

	/**
	 * Register ability category and ability via WordPress Abilities API hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( function_exists( 'wp_register_ability_category' ) ) {
			add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		}

		if ( function_exists( 'wp_register_ability' ) ) {
			add_action( 'wp_abilities_api_init', array( $this, 'register_ability' ) );
		}
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
				'label' => __( 'Slug Automator', 'slug-automator' ),
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
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'description'       => __( 'Optional. A post ID to check edit permission against.', 'slug-automator' ),
						),
					),
					'required'   => array( 'title' ),
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
		$slug = $this->slugifier->generate( $input['title'] );

		if ( null === $slug ) {
			return new \WP_Error(
				'slug_automator_generate_failed',
				__( 'Failed to generate slug. AI may be unavailable.', 'slug-automator' )
			);
		}

		return array( 'slug' => $slug );
	}

	/**
	 * Check whether the current user can execute this ability.
	 *
	 * If 'context' is a numeric string, it is treated as a post ID and
	 * edit_post permission is checked for that specific post.
	 * Otherwise, the general edit_posts capability is required.
	 *
	 * @param array $input Input data, optionally containing 'context' (a numeric post ID).
	 *
	 * @return bool|\WP_Error
	 */
	public function permission_callback( array $input ): bool|\WP_Error {
		$post_id = isset( $input['context'] ) && is_numeric( $input['context'] ) ? absint( $input['context'] ) : null;

		if ( $post_id ) {
			$post = get_post( $post_id );

			if ( ! $post ) {
				return new \WP_Error(
					'post_not_found',
					/* translators: %d: Post ID. */
					sprintf( __( 'Post with ID %d not found.', 'slug-automator' ), $post_id )
				);
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return new \WP_Error(
					'insufficient_capabilities',
					__( 'You do not have permission to generate a slug for this post.', 'slug-automator' )
				);
			}

			$post_type_obj = get_post_type_object( get_post_type( $post_id ) );

			if ( ! $post_type_obj || empty( $post_type_obj->show_in_rest ) ) {
				return false;
			}

			return true;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'insufficient_capabilities',
				__( 'You do not have permission to generate slugs.', 'slug-automator' )
			);
		}

		return true;
	}
}
