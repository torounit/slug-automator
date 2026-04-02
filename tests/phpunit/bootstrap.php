<?php

/**
 * PHPUnit bootstrap file.
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

// Composer autoloader.
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// WordPress テスト環境のパス（wp-phpunit を利用）
$wp_tests_dir = getenv('WP_TESTS_DIR');

if (! $wp_tests_dir) {
	// composer の wp-phpunit パッケージのパスを自動解決する。
	$wp_tests_dir = dirname(__DIR__, 2) . '/vendor/wp-phpunit/wp-phpunit';
}

if (! file_exists($wp_tests_dir . '/includes/functions.php')) {
	echo "Could not find WordPress test suite at '{$wp_tests_dir}'." . PHP_EOL;
	exit(1);
}

// プラグインをロードするコールバックを登録する。
tests_add_filter(
	'muplugins_loaded',
	function (): void {
		require dirname(__DIR__, 2) . '/slug-automator.php';
	}
);

require $wp_tests_dir . '/includes/bootstrap.php';
