<?php
/**
 * Auto Slug Class
 *
 * Automatically generates a slug when a post is published without one.
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

namespace Slug_Automator;

/**
 * Class Auto_Slug
 */
class Auto_Slug {

	/**
	 * Constructor.
	 *
	 * @param Slugifier $slugifier Slugifier instance.
	 */
	public function __construct( private readonly Slugifier $slugifier ) {}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'wp_insert_post_data', array( $this, 'on_publish' ), 10, 2 );
	}

	/**
	 * Generate a slug when a post is published without one.
	 *
	 * @param array $data    Sanitized post data.
	 * @param array $postarr Raw post data as supplied by the caller.
	 *
	 * @return array Modified post data.
	 */
	public function on_publish( array $data, array $postarr ): array {
		if ( 'publish' !== $data['post_status'] || ! empty( $postarr['post_name'] ) ) {
			return $data;
		}

		$slug = $this->slugifier->generate( $data['post_title'] );

		if ( null !== $slug ) {
			$data['post_name'] = $slug;
		}

		return $data;
	}
}
