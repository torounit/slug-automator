# Slug Automator

Contributors: Toro_Unit  
Donate link: https://www.paypal.me/torounit  
Tags: slug, permalink, ai  
Requires at least: 7.0  
Tested up to: 7.0  
Stable tag: 0.5.0  
Requires PHP: 8.2  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Automatically generates URL-friendly slugs from post titles using the WordPress AI Client.

## Description

Slug Automator automatically generates slugs from WordPress post titles using the [AI Client](https://make.wordpress.org/core/2026/03/24/introducing-the-ai-client-in-wordpress-7-0/) introduced in WordPress 7.0.

Writing post titles in any other non-English language? Slug Automator uses AI to translate them into clean, URL-friendly English slugs — no manual input required. Automatic generation on publish runs only when the slug field is empty, so any slug you've set manually is never overwritten. You can also generate or regenerate a slug on demand with the "Generate Slug" button in the block editor.

Because it relies on the WordPress AI Client, it works with any AI provider configured on your site (Anthropic, Google, OpenAI, and more).

GitHub: [https://github.com/torounit/slug-automator](https://github.com/torounit/slug-automator)

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

Not automatically. Auto-generation on publish runs only when the slug field is empty. If you want to replace an existing slug, you can do so manually with the "Generate Slug" button in the block editor.

## Changelog

### 0.5.0

* Add `wpai_has_ai_credentials` filter to allow third-party plugins to declare AI connector availability for connectors that don't rely on API key settings.

### 0.4.1

* Optimize model preferences for slug generation to improve output quality.
* Update README.

### 0.4.0

* Fix duplicate slug on regeneration by passing the current slug as an exclusion hint to the AI.
* Improve AI prompt to require semantic English translation and prohibit transliteration.
* Refactor context schema to `{type, id}` and tighten permission checks in the slug ability.

### 0.3.1

* Add "Generate Slug" button to the block editor using the WordPress Abilities API.

### 0.2.0

* Add support for Gemini 3.1 Flash Lite model.

### 0.1.8

* Fix release zip to include build assets.

### 0.1.5

* WordPress Plugin Directory release.

### 0.1.0

* Initial release.
