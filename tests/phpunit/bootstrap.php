<?php
/**
 * PHPUnit bootstrap file
 */

// Require composer dependencies.
require_once dirname( __DIR__ , 2) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// See temp dir.
if ( ! $_tests_dir ) {
	$_try_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
	if ( file_exists( $_try_tests_dir . '/includes/functions.php' ) ) {
		$_tests_dir = $_try_tests_dir;
	}
	unset( $_try_tests_dir );
}

// Next, try the WP_PHPUNIT composer package.
if ( ! $_tests_dir ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

// See if we're installed inside an existing WP dev instance.
if ( ! $_tests_dir ) {
	$_try_tests_dir = __DIR__ . '/../../../../../tests/phpunit';
	if ( file_exists( $_try_tests_dir . '/includes/functions.php' ) ) {
		$_tests_dir = $_try_tests_dir;
	}
}
// Fallback.
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}


if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function (): void {
		// Load the AI provider plugins so the AI tests can call real models when an API key is configured.
		foreach ( glob( WP_PLUGIN_DIR . '/ai-provider-for-*/plugin.php' ) ?: [] as $provider_plugin ) {
			require $provider_plugin;
		}

		require dirname(__DIR__, 2) . '/slug-automator.php';
	}
);

// Access tokens issued by Workload Identity Federation (sk-ant-oat...) must be sent as a bearer token,
// while the Anthropic provider sends the configured key as x-api-key.
tests_add_filter(
	'http_request_args',
	function ( array $args, string $url ): array {
		if ( ! str_starts_with( $url, 'https://api.anthropic.com/' ) || empty( $args['headers'] ) ) {
			return $args;
		}

		foreach ( $args['headers'] as $name => $value ) {
			if ( 'x-api-key' === strtolower( (string) $name ) && str_starts_with( (string) $value, 'sk-ant-oat' ) ) {
				unset( $args['headers'][ $name ] );
				$args['headers']['Authorization'] = 'Bearer ' . $value;
			}
		}

		return $args;
	},
	10,
	2
);


// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
