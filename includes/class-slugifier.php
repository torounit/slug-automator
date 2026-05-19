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
		$avoid_sanitized = null;
		if ( null !== $avoid && '' !== $avoid ) {
			$candidate = sanitize_title( $avoid );
			if ( '' !== $candidate ) {
				$avoid_sanitized = $candidate;
			}
		}

		$slug = $this->translate_with_wp_ai( $title, $avoid_sanitized );

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
			->using_model_preference( ...$this->model_preferences() )
			->generate_text();

		return $this->parse_response( $result );
	}

	/**
	 * Returns the preferred models to use for slug generation, in order of preference.
	 *
	 * @return array<int, array{0: string, 1: string}>
	 */
	protected function model_preferences(): array {
		$models = array(
			array( 'anthropic', 'claude-haiku-4-5' ),
			array( 'google', 'gemini-3.1-flash-lite' ),
			array( 'openai', 'gpt-5.4-nano' ),
			array( 'openai', 'gpt-5-nano' ),
		);

		return apply_filters( 'slug_automator_model_preferences', $models );
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
