# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

This is **ibexa/admin-ui**, the core Back Office interface bundle for Ibexa DXP (Digital Experience Platform). It provides the administrative UI for content management, user management, and system configuration.

## Commands

### PHP (run from package root)

```bash
# Code style
composer fix-cs           # Auto-fix PHP code style
composer check-cs         # Check code style (dry-run)

# Testing
composer test             # Run all tests
composer test-unit        # Unit tests only (tests/bundle + tests/lib)
composer test-integration # Integration tests only (tests/integration)

# Static analysis
composer phpstan          # PHPStan level 8
```

### JavaScript/TypeScript (run from package root)

```bash
yarn test                 # Run all checks (prettier + TypeScript + ESLint)
yarn fix                  # Auto-fix all frontend issues

# Individual checks
yarn prettier-test        # Code formatting check
yarn ts-test              # TypeScript type checking
yarn eslint-test          # ESLint linting
```

### Frontend Assets

Assets are built from the project root (not package root) using Webpack Encore:
```bash
yarn ibexa:dev            # Development build
yarn ibexa:build          # Production build
```

Entry points are defined in `src/bundle/Resources/encore/ibexa.js.config.js` and `ibexa.css.config.js`.

## Architecture

### Directory Structure

```
src/
├── lib/           # Business logic (Ibexa\AdminUi\)
├── bundle/        # Symfony bundle (Ibexa\Bundle\AdminUi\)
│   ├── Controller/      # Route controllers
│   ├── DependencyInjection/
│   │   └── Compiler/    # TabPass, ComponentPass, etc.
│   ├── Resources/
│   │   ├── config/      # Service definitions, routing
│   │   ├── public/      # Static assets (js/, scss/, ts/)
│   │   └── encore/      # Webpack Encore configs
│   └── ui-dev/          # Modern React/TypeScript modules
│       └── src/modules/ # UDW, content-tree, multi-file-upload, etc.
└── contracts/     # Public API interfaces (Ibexa\Contracts\AdminUi\)

tests/
├── lib/           # Unit tests for src/lib
├── bundle/        # Unit tests for src/bundle
└── integration/   # Integration tests with test kernel
```

### Namespace Conventions

- `Ibexa\AdminUi\` - Internal library code
- `Ibexa\Bundle\AdminUi\` - Symfony bundle integration
- `Ibexa\Contracts\AdminUi\` - Public API (interfaces, value objects)
- `Ibexa\Tests\AdminUi\` - Unit tests

### Compiler Passes

The bundle registers several compiler passes in `IbexaAdminUiBundle.php`:
- `TabPass` - Register custom tabs
- `ComponentPass` - UI component registration
- `UiConfigProviderPass` - UI configuration providers
- `LimitationFormMapperPass` / `LimitationValueMapperPass` - Permission limitations
- `FieldTypeFormMapperDispatcherPass` - Field type form handling

### Frontend Architecture

**Legacy JavaScript** (`src/bundle/Resources/public/js/`):
- Core utilities, field type handlers, sidebar behaviors
- 66+ scripts bundled into `ibexa-admin-ui-layout-js`

**Modern Modules** (`src/bundle/ui-dev/src/modules/`):
- React/TypeScript components
- Universal Discovery Widget (UDW)
- Content tree, multi-file upload, sub-items

**TypeScript Configuration**:
- `tsconfig.json` includes path aliases for cross-package imports
- Self-reference: `@ibexa-admin-ui/*` maps to current package
- Generated via `yarn postinstall`

## Testing

### Running Single Tests

```bash
# Single unit test file
./vendor/bin/phpunit -c phpunit.xml tests/lib/Path/To/TestFile.php

# Single integration test
./vendor/bin/phpunit -c phpunit.integration.xml tests/integration/Path/To/TestFile.php

# Filter by test method
./vendor/bin/phpunit -c phpunit.xml --filter testMethodName
```

### Integration Test Environment

Integration tests use:
- `AdminUiIbexaTestKernel` custom test kernel
- SQLite database (configurable via `DATABASE_URL`)
- DAMA Doctrine Test Bundle for transaction isolation

## Code Style

- PHP: `declare(strict_types=1)` required in all files
- PHPStan level 8 with baseline
- Frontend: Prettier + ESLint via `@ibexa/eslint-config`
