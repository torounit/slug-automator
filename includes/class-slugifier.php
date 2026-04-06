<?php
/**
 * Slugifier Class
 *
 * This class is responsible for generating slugs from post titles, utilizing WordPress's AI capabilities for translation when available, and falling back to sanitized titles when not.
 *
 * @package Slug_Automator
 */

namespace Slug_Automator;

/**
 * Class Slugifier
 */
class Slugifier {

	/**
	 * Generate a slug from the given title.
	 *
	 * If WordPress 7.0 or later AI capabilities are available, it will generate the slug after translation.
	 *
	 * @param string $title Post title.
	 *
	 * @return string|null Generated slug or null if AI translation is not available.
	 */
	public function generate( string $title ): ?string {
		$slug = $this->translate_with_wp_ai( $title );

		if ( null === $slug ) {
			return null;
		}

		$slug = sanitize_title( $slug );

		return '' !== $slug ? $slug : null;
	}

	/**
	 * Translate the title to English using WordPress AI capabilities.
	 *
	 * Uses the WordPress 7.0 or later AI API when available.
	 *
	 * @param string $title Original title.
	 *
	 * @return string|null Translated text. Null if AI is not available.
	 */
	protected function translate_with_wp_ai( string $title ): ?string {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'result' => array( 'type' => 'string' ),
			),
			'required'   => array( 'result' ),
		);

		$result = wp_ai_client_prompt( "Title: {$title}" )
			->using_system_instruction(
				'Convert the provided title into a concise English URL slug. ' .
				'Use only lowercase letters, numbers, and hyphens. ' .
				'Do not use spaces or special characters. ' .
				'Keep it to a maximum of 5 words.'
			)
			->using_temperature( 0.1 )
			->as_json_response( $schema )
			->using_model_preference(
				array(
					'anthropic',
					'claude-haiku-4-5',
				),
				array(
					'google',
					'gemini-2.5-flash',
				),
				array(
					'openai',
					'gpt-4o-mini',
				),
				array(
					'openai',
					'gpt-4.1',
				),
			)
			->generate_text();

		return $this->parse_response( $result );
	}

	/**
	 * Parse the AI response JSON and extract the raw slug string.
	 *
	 * @param string|\WP_Error $result Raw response from the AI.
	 *
	 * @return string|null Raw slug string, or null if the response is invalid.
	 */
	protected function parse_response( string|\WP_Error $result ): ?string {
		if ( is_wp_error( $result ) ) {
			return null;
		}

		$data = json_decode( $result, true );

		if ( ! is_array( $data ) || empty( $data['result'] ) ) {
			return null;
		}

		return (string) $data['result'];
	}
}
