<?php
/**
 * Slugifier Class
 *
 * This class is responsible for generating slugs from post titles, utilizing WordPress's AI capabilities for translation when available.
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

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
	 * @param string      $title Post title.
	 * @param string|null $avoid An existing slug to avoid returning.
	 *
	 * @return string|null Generated slug or null if AI translation is not available.
	 */
	public function generate( string $title, ?string $avoid = null ): ?string {
		$slug = $this->translate_with_wp_ai( $title, $avoid );

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
	 * @param string      $title Original title.
	 * @param string|null $avoid An existing slug that the result must differ from.
	 *
	 * @return string|null Translated text. Null if AI is not available.
	 */
	protected function translate_with_wp_ai( string $title, ?string $avoid = null ): ?string {
		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			return null;
		}

		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'result' => array( 'type' => 'string' ),
			),
			'required'   => array( 'result' ),
		);

		$user_prompt = "Title: {$title}";
		if ( null !== $avoid && '' !== $avoid ) {
			$user_prompt .= "\nDo not produce this slug: \"{$avoid}\". Generate a clearly different slug.";
		}

		$result = wp_ai_client_prompt( $user_prompt )
			->using_system_instruction(
				'Translate the provided title into concise English, then create a URL slug from the English translation. ' .
				'If the title is not in English, translate it semantically into English. ' .
				'Do NOT transliterate or romanize non-English text. ' .
				'For example, the Japanese title "こんにちは" should become "hello", not "konnichiha". ' .
				'Use only lowercase ASCII letters, numbers, and hyphens. ' .
				'Do not use spaces or special characters.'
			)
			->using_temperature( 0.7 )
			->as_json_response( $schema )
			->using_model_preference(
				array(
					'anthropic',
					'claude-haiku-4-5',
				),
				array(
					'google',
					'gemini-3.1-flash-lite',
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
