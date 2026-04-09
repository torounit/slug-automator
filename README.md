# Slug Automator

Contributors: Toro_Unit  
Tags: slug, permalink, ai  
Requires at least: 7.0  
Tested up to: 7.0  
Stable tag: 0.1.2  
Requires PHP: 8.2  
License: GPLv2 or later  
License URI: <https://www.gnu.org/licenses/gpl-2.0.html>  

Automatically generates URL-friendly slugs from post titles using the WordPress AI Client.

## Description

Slug Automator automatically generates slugs from WordPress post titles using the [AI Client](https://make.wordpress.org/core/2026/03/24/introducing-the-ai-client-in-wordpress-7-0/) introduced in WordPress 7.0.

Writing post titles in Japanese or any other non-English language? Slug Automator uses AI to translate them into clean, English URL-friendly slugs — no manual input required. It only runs when the slug field is empty, so any slug you set yourself will never be overwritten.

Because it relies on the WordPress AI Client, it works with any AI provider configured on your site (Anthropic, Google, OpenAI, and more).

### Features

* Automatically generates slugs from post titles via AI
* Provider-agnostic — uses whichever AI provider is configured in WordPress
* Works directly in the block editor

## Installation

1. Upload the plugin files to the `/wp-content/plugins/slug-automator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Make sure an AI provider is configured under **Settings > Connectors** in WordPress.

## Frequently Asked Questions

### Which AI service does this plugin use?

This plugin uses the WordPress AI Client (requires WordPress 7.0 or later). It works with any AI provider configured by the site administrator under **Settings > Connectors**.

### Does it overwrite slugs I have already set?

No. The plugin only generates a slug when the slug field is empty.

## Changelog

### 0.1.0

* Initial release.
