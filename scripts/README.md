# Component Migration Tools

Automated orchestration for migrating legacy UI components to Ibexa design system.

## Quick Start

```bash
cd scripts/

# Migrate specific chunk
npm run migrate:button -- --chunk CHUNK_03

# Migrate from chunk 3 onwards
npm run migrate:button -- --start-from 3

# Show status
npm run migrate:status -- --component button
```

## Architecture

### Branch Strategy

Migrations run on **isolated branches** that are **never merged back**:

- `ds-development` - Tool development and manual migrations
- `migrate/buttons` - Automated button migrations only
- `migrate/badges` - Automated badge migrations only (future)

This keeps migration commits separate from tool commits for clean history.

## How It Works

1. **Branch Creation** - Auto-creates `migrate/buttons` branch if it doesn't exist
2. **Switches Branch** - Automatically switches to migration branch
3. **Processes Chunks** - Migrates files using OpenCode CLI
4. **Validates** - Runs `yarn test` after each chunk
5. **Commits** - Creates commit on migration branch
6. **Retries** - Up to 3 attempts with automatic rollback
7. **Skip on Failure** - Continues to next chunk if max retries exceeded
8. **Stays Isolated** - Never merges back to base branch

## Usage

### Migrate Single Chunk

```bash
npm run migrate:button -- --chunk CHUNK_03
```

### Migrate Multiple Chunks

```bash
# Start from chunk 3 onwards
npm run migrate:button -- --start-from 3

# Migrate all remaining chunks
npm run migrate:button
```

### Check Status

```bash
npm run migrate:status -- --component button
```

### Dry Run

```bash
npm run migrate:button -- --dry-run
```

### List Available Components

```bash
npm run migrate -- --list-components
```

## Adding New Components

### 1. Create Migration Directory

```bash
mkdir -p migrations/badge/{prompts,.migration-state}
```

### 2. Add Migration Files

- `MIGRATION_PLAN.txt` - Chunk definitions (format: `CHUNK_ID|FILE_PATH|COUNT|UNMAPPED|CATEGORY|NOTES`)
- `MIGRATION_RULES.md` - Comprehensive migration rules and mappings
- `prompts/migrate-chunk.txt` - OpenCode prompt template with variables

### 3. Add Configuration

Edit `config/components.config.ts`:

```typescript
export const badgeConfig: ComponentConfig = {
  name: 'badge',
  displayName: 'Badge',
  planFile: '../migrations/badge/MIGRATION_PLAN.txt',
  rulesFile: '../migrations/badge/MIGRATION_RULES.md',
  promptTemplate: '../migrations/badge/prompts/migrate-chunk.txt',
  
  branch: {
    name: 'migrate/badges',
    createIfMissing: true,
    switchAutomatically: true,
    mergeBack: false  // CRITICAL: Always false!
  },
  
  commit: {
    prefix: 'Migrate badges:',
    includeFileList: true,
    includeStats: true
  },
  
  validation: {
    enabled: true,
    command: 'yarn test',
    timeout: 300
  },
  
  retry: {
    maxAttempts: 3,
    revertOnFailure: true
  }
};
```

Register in `componentRegistry`.

### 4. Run Migration

```bash
npm run migrate:badge -- --chunk CHUNK_01
```

## File Structure

```
scripts/
├── migrate-component.ts         # Main orchestrator
├── types.ts                     # TypeScript definitions
├── package.json                 # Dependencies
├── tsconfig.json                # TypeScript config
├── config/
│   ├── components.config.ts    # Component configurations
│   └── opencode.config.ts      # OpenCode CLI settings
├── lib/
│   ├── branch-manager.ts       # Branch isolation logic
│   ├── git.ts                  # Git operations
│   ├── opencode.ts             # OpenCode CLI wrapper
│   ├── validation.ts           # Frontend validation (yarn test)
│   ├── parser.ts               # Parse MIGRATION_PLAN.txt
│   ├── prompt-builder.ts       # Build OpenCode prompts
│   └── logger.ts               # Colored console output
└── README.md                   # This file

migrations/
├── button/
│   ├── MIGRATION_PLAN.txt
│   ├── MIGRATION_RULES.md
│   ├── prompts/
│   │   └── migrate-chunk.txt
│   └── .migration-state/       # Runtime state (gitignored)
│       ├── logs/
│       ├── failed-chunks.txt
│       └── progress.txt
└── badge/                      # Future component
    └── ...
```

## Troubleshooting

### Wrong Branch

If you're not on the migration branch:

```bash
git checkout migrate/buttons
npm run migrate:button -- --chunk CHUNK_03
```

### Failed Chunk

Check logs:

```bash
cat ../migrations/button/.migration-state/logs/CHUNK_03-attempt1.log
```

View failed chunks:

```bash
cat ../migrations/button/.migration-state/failed-chunks.txt
```

### Reset Failed Chunk

```bash
git restore .
git clean -fd
npm run migrate:button -- --chunk CHUNK_03
```

### View Progress

```bash
cat ../migrations/button/.migration-state/progress.txt
```

## Configuration

See `config/components.config.ts` for all settings:

- Branch names
- Commit message format
- Validation settings (command, timeout)
- Retry behavior (max attempts, revert on failure)
- OpenCode options (model, timeout)

## Development

### Type Check

```bash
npm run type-check
```

### Build

```bash
npm run build
```

### Run Directly with tsx

```bash
tsx migrate-component.ts --component button --chunk CHUNK_03
```

## Key Features

✅ **Component-Agnostic** - Easy to add new component types (badges, alerts, etc.)  
✅ **Branch Isolation** - Migrations on separate branches, never merged back  
✅ **Type-Safe** - Comprehensive TypeScript types catch errors at compile time  
✅ **Retry Logic** - Automatic retries with rollback on failure  
✅ **Skip on Failure** - Continues to next chunk if max retries exceeded  
✅ **Progress Tracking** - Saves progress to text file for resumability  
✅ **Real-Time Output** - Streams OpenCode and validation output  
✅ **Colored Logging** - Clear visual feedback with progress indicators  

## Notes

- Migrations are **NOT** meant to be merged back to the base branch
- Each component gets its own isolated branch (`migrate/*`)
- Tool commits stay on base branch, migration commits stay on component branch
- This keeps commit history clean and organized
- Use separate PRs for tool changes vs. migrations if needed
