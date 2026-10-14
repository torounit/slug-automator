<?php

/**
 * Tests for the Slugifier class against real AI models.
 *
 * These tests are skipped unless an AI provider API key (e.g. ANTHROPIC_API_KEY)
 * is configured. See AGENTS.md for how to set it up locally.
 *
 * @package Slug_Automator\Tests
 */

declare(strict_types=1);

namespace Slug_Automator\Tests\Integration;

use Slug_Automator\Slugifier;

/**
 * Class Slugifier_Test
 *
 * @group ai
 */
class Slugifier_Test extends \WP_UnitTestCase {

	private const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

	private Slugifier $slugifier;

	public function set_up(): void {
		parent::set_up();

		if ( ! wp_ai_client_prompt( 'ping' )->is_supported_for_text_generation() ) {
			$this->markTestSkipped( 'No AI provider is configured. Set an API key such as ANTHROPIC_API_KEY to run the AI tests.' );
		}

		$this->slugifier = new Slugifier();
	}

	/**
	 * Titles, the words their slugs are expected to contain, and romanizations they must not contain.
	 *
	 * Each expected entry lists synonyms, any of which satisfies it, so that equivalent translations pass.
	 *
	 * @return array<string, array{0: string, 1: list<list<string>>, 2: list<string>}>
	 */
	public function data_titles(): array {
		return [
			'japanese greeting' => [ 'こんにちは', [ [ 'hello', 'hi', 'greeting' ] ], [ 'konnichiha', 'konnichiwa' ] ],
			'japanese sentence' => [ '東京の天気', [ [ 'tokyo' ], [ 'weather', 'forecast', 'climate' ] ], [ 'tenki' ] ],
			'mixed scripts'     => [ 'WordPressでブログを始める', [ [ 'wordpress' ], [ 'blog' ] ], [ 'hajimeru' ] ],
			'english title'     => [ 'Hello World', [ [ 'hello' ], [ 'world' ] ], [] ],
		];
	}

	/**
	 * The title is translated semantically, not transliterated.
	 *
	 * @dataProvider data_titles
	 *
	 * @param string             $title     Post title.
	 * @param list<list<string>> $expected  Synonym groups; the slug must contain a word from each group.
	 * @param list<string>       $forbidden Words the slug must not contain.
	 */
	public function test_generate_translates_title( string $title, array $expected, array $forbidden ): void {
		$slug = $this->slugifier->generate( $title );

		$this->assertIsString( $slug );
		$this->assertMatchesRegularExpression( self::SLUG_PATTERN, $slug );

		// Match by word prefix so that inflections such as "blogging" are accepted.
		$words = explode( '-', $slug );
		foreach ( $expected as $synonyms ) {
			$matched = array_filter(
				$words,
				static fn ( string $word ): bool => (bool) array_filter(
					$synonyms,
					static fn ( string $synonym ): bool => str_starts_with( $word, $synonym )
				)
			);
			$this->assertNotEmpty( $matched, sprintf( 'Slug "%s" contains none of: %s', $slug, implode( ', ', $synonyms ) ) );
		}

		foreach ( $forbidden as $word ) {
			$this->assertStringNotContainsString( $word, $slug );
		}
	}

	/**
	 * A slug different from the one to avoid is generated.
	 */
	public function test_generate_avoids_given_slug(): void {
		$avoid = $this->slugifier->generate( '東京の天気' );
		$this->assertIsString( $avoid );

		$slug = $this->slugifier->generate( '東京の天気', $avoid );

		$this->assertIsString( $slug );
		$this->assertMatchesRegularExpression( self::SLUG_PATTERN, $slug );
		$this->assertNotSame( $avoid, $slug );
	}
}
