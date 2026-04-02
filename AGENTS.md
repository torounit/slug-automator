# AGENTS.md

## Commands

### PHP

```bash
composer install          # Install dependencies
composer lint             # Run PHPCS (uses phpcs.xml.dist)
composer lint:fix         # Auto-fix with PHPCBF
composer test             # Run all PHPUnit tests
vendor/bin/phpunit --filter test_name  # Run a single test
```

### JavaScript / TypeScript

```bash
npm install        # Install dependencies
npm run build      # Production build → build/
npm start          # Development watch mode
npm run lint:js    # ESLint
npm run lint:style # Stylelint
```

## Coding Standards

Follows WordPress Coding Standards (see `phpcs.xml.dist`). Namespaces and class names use underscore-separated capitalized words (e.g. `Slug_Automator\Slug_Automator`), not PascalCase.
