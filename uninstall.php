<?php

/**
 * Uninstall Slug Automator
 *
 * @package Slug_Automator
 */

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// プラグインが保存したオプションを削除する。
delete_option('slug_automator_settings');
