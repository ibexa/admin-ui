# Validate Frontend Changes - Ibexa Admin UI

## Description
This skill validates Twig template and JavaScript changes in Ibexa packages, specifically after design system component migrations. It runs frontend tests, builds assets, and provides a testing checklist.

## When to Use
- After migrating HTML buttons to design system button components in Twig
- After modifying JavaScript or TypeScript files
- Before committing frontend changes
- When troubleshooting build or runtime issues with UI components

## Prerequisites
- Node.js and Yarn installed
- Assets may have been modified but not yet validated
- Working directory should be the project root or admin-ui package

## Validation Steps

### 1. Frontend Code Quality Check
**Location:** Run from the affected Ibexa package (e.g., `vendor/ibexa/admin-ui`)

```bash
cd vendor/ibexa/[package-name]
yarn test           # Runs Prettier, TypeScript, ESLint
```

**What this validates:**
- JavaScript/TypeScript syntax and type safety
- Code formatting consistency
- ESLint rules compliance
- No TypeScript compilation errors

**Expected result:** All tests pass with no errors

### 2. Asset Build Process
**Location:** Run from project root

```bash
cd /path/to/project-root
yarn ibexa:dev      # Development build (faster)
# OR
yarn ibexa:build    # Production build (minified)
```

**What this validates:**
- Webpack Encore compiles successfully
- No missing dependencies
- JavaScript bundles build correctly
- SCSS compiles with new component styles
- Entry points resolve properly

**Expected result:** Build completes without errors, assets generated in `public/assets/ibexa/build/`

### 3. Cache Clear
```bash
php bin/console cache:clear
```

**Purpose:** Ensures Symfony uses fresh asset manifests and templates

## Common Issues and Solutions

### Build Failures

**Issue:** `yarn ibexa:dev` fails with module not found
**Solution:**
- Verify design system packages are installed

**Issue:** TypeScript compilation errors
**Solution:**
- Run `yarn ts-test` in the package to see specific errors
- Check type definitions for design system components
- Verify `tsconfig.json` paths are correct

## Package-Specific Notes

### admin-ui Package
- Entry points: `src/bundle/Resources/encore/ibexa.js.config.js` and `ibexa.css.config.js`

## Quick Validation Script

```bash
#!/bin/bash
# Run from project root

PACKAGE_PATH="vendor/ibexa/admin-ui"  # Change as needed
PROJECT_ROOT="/path/to/project"        # Change to actual path

echo "=== Step 1: Frontend Code Quality ==="
cd "$PROJECT_ROOT/$PACKAGE_PATH"
yarn test || { echo "Frontend tests failed"; exit 1; }

echo "=== Step 2: Build Assets ==="
cd "$PROJECT_ROOT"
yarn ibexa:dev || { echo "Build failed"; exit 1; }

echo "=== Step 3: Clear Cache ==="
php bin/console cache:clear

echo "✅ Automated validation complete!"
echo "👉 Now test manually in browser"
```

## Success Criteria
- ✅ `yarn test` passes in the package
- ✅ `yarn ibexa:dev` builds without errors
- ✅ No console errors in browser
- ✅ All button functionality works as before
- ✅ Visual appearance matches design system
- ✅ Cross-browser compatibility (if tested)

## Next Steps After Validation
1. Commit changes with descriptive message
2. DO NOT PUSH changes to the repository
3. DO NOT CREATE PULL REQUEST
