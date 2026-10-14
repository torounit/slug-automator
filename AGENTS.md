# AGENTS.md

## Commands

### PHP

```bash
composer install          # Install dependencies
composer lint             # Run PHPCS (uses phpcs.xml.dist)
composer lint:fix         # Auto-fix with PHPCBF
npm run test:php     # Run PHPUnit tests on wp-env
```

The AI tests (`@group ai`) are skipped unless an AI provider API key is configured. To run them locally, create `.wp-env.test.override.json` (ignored by git), restart the test environment, and run `npm run test:php:base -- --group ai` to run only them:

```json
{
	"config": {
		"ANTHROPIC_API_KEY": "sk-ant-..."
	}
}
```

### JavaScript / TypeScript

```bash
npm install        # Install dependencies
npm run build      # Production build → build/
npm start          # Development watch mode
npm run format     # Format JS/TS/CSS/JSON files
npm run lint:js    # ESLint
npm run lint:style # Stylelint
```

## Coding Standards

Follows WordPress Coding Standards (see `phpcs.xml.dist`). Namespaces and class names use underscore-separated capitalized words (e.g. `Slug_Automator\Slug_Automator`), not PascalCase.
