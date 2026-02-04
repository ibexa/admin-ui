#!/usr/bin/env node
/**
 * Component Migration Orchestrator
 * Automates migration of legacy UI components to design system
 */

import { Command } from 'commander';
import { writeFileSync, readFileSync, existsSync, mkdirSync } from 'fs';
import { join } from 'path';
import { Chunk, ComponentConfig, MigrationSession } from './types.js';
import { getComponentConfig, listComponents } from './config/components.config.js';
import { opencodeConfig } from './config/opencode.config.js';
import { BranchManager } from './lib/branch-manager.js';
import { git } from './lib/git.js';
import { logger } from './lib/logger.js';
import { parseChunks, getChunkById } from './lib/parser.js';
import { buildPrompt } from './lib/prompt-builder.js';
import { runOpenCode } from './lib/opencode.js';
import { runValidation } from './lib/validation.js';

const program = new Command();

program
  .name('migrate-component')
  .description('Automated component migration orchestration')
  .version('1.0.0');

program
  .option('-c, --component <name>', 'Component to migrate (button, badge, etc.)')
  .option('--chunk <id>', 'Process specific chunk only')
  .option('--start-from <number>', 'Start from chunk number', parseInt)
  .option('--dry-run', 'Show what would be done without making changes')
  .option('--status', 'Show migration status')
  .option('--list-components', 'List available components');

program.parse();

const options = program.opts();

async function main() {
  try {
    // List components
    if (options.listComponents) {
      logger.header('Available Components');
      const components = listComponents();
      components.forEach(c => logger.info(`  - ${c}`));
      return;
    }

    // Validate component option
    if (!options.component) {
      logger.error('--component is required');
      logger.info('Available: ' + listComponents().join(', '));
      logger.info('Example: npm run migrate -- --component button --chunk CHUNK_03');
      process.exit(1);
    }

    const config = getComponentConfig(options.component);
    logger.header(`${config.displayName} Migration Orchestrator`);

    // Show status
    if (options.status) {
      await showStatus(config);
      return;
    }

    // Validate environment
    await validateEnvironment(config);

    // Parse chunks
    const allChunks = parseChunks(config.planFile);
    logger.success(`Loaded ${allChunks.length} chunks from plan`);

    // Dry run
    if (options.dryRun) {
      logger.warn('DRY RUN MODE - No changes will be made');
      logger.blank();
      allChunks.forEach((chunk, i) => {
        logger.info(`[${i + 1}/${allChunks.length}] ${chunk.id}: ${chunk.files.length} files`);
      });
      return;
    }

    // Initialize branch manager
    const branchManager = new BranchManager(config);
    const session = await branchManager.startMigration();

    // Determine which chunks to process
    let chunks: Chunk[] = [];
    if (options.chunk) {
      const chunk = getChunkById(allChunks, options.chunk);
      if (!chunk) {
        throw new Error(`Chunk not found: ${options.chunk}`);
      }
      chunks = [chunk];
    } else if (options.startFrom) {
      chunks = allChunks.slice(options.startFrom - 1);
    } else {
      chunks = allChunks;
    }

    session.totalChunks = chunks.length;
    logger.info(`Processing ${chunks.length} chunks`);
    logger.blank();

    // Process each chunk
    for (let i = 0; i < chunks.length; i++) {
      const chunk = chunks[i];
      logger.progress(i + 1, chunks.length, chunk.id);

      const success = await processChunkWithRetry(chunk, config, i + 1);

      if (success) {
        session.completedChunks.push(chunk.id);
        await createCommit(chunk, config);
      } else {
        // Skip and continue (per user requirement #4)
        session.failedChunks.push(chunk.id);
        await saveFailedChunk(chunk, config);
        logger.warn(`Skipping ${chunk.id} - added to failed list`);
      }

      // Save progress
      await saveProgress(session, config);
    }

    // Finish migration
    await branchManager.finishMigration(session);

  } catch (error: any) {
    logger.error(`Fatal error: ${error.message}`);
    process.exit(1);
  }
}

async function validateEnvironment(config: ComponentConfig): Promise<void> {
  logger.info('Validating environment...');

  // Check OpenCode
  try {
    git.getCurrentBranch();  // Test git access
    logger.success('Git repository detected');
  } catch {
    throw new Error('Not in a git repository');
  }

  // Check for uncommitted changes
  if (await git.hasUncommittedChanges()) {
    throw new Error('Uncommitted changes detected. Please commit or stash first.');
  }

  // Check required files
  if (!existsSync(config.planFile)) {
    throw new Error(`Migration plan not found: ${config.planFile}`);
  }
  if (!existsSync(config.rulesFile)) {
    throw new Error(`Migration rules not found: ${config.rulesFile}`);
  }
  if (!existsSync(config.promptTemplate)) {
    throw new Error(`Prompt template not found: ${config.promptTemplate}`);
  }

  logger.success('Environment validation passed');
  logger.blank();
}

async function processChunkWithRetry(
  chunk: Chunk,
  config: ComponentConfig,
  attemptNum: number
): Promise<boolean> {
  for (let attempt = 1; attempt <= config.retry.maxAttempts; attempt++) {
    logger.section(`Processing ${chunk.id} (Attempt ${attempt}/${config.retry.maxAttempts})`);

    // Run migration
    const migrationSuccess = await runMigration(chunk, config, attempt);

    if (!migrationSuccess) {
      logger.error(`Migration failed (attempt ${attempt})`);
      if (attempt < config.retry.maxAttempts && config.retry.revertOnFailure) {
        logger.warn('Reverting and retrying...');
        await git.restore();
        continue;
      } else {
        logger.error('Max attempts reached - SKIPPING');
        if (config.retry.revertOnFailure) {
          await git.restore();
        }
        return false;
      }
    }

    // Validate changes
    if (config.validation.enabled) {
      const validationSuccess = await validateChanges(chunk, config);

      if (!validationSuccess) {
        logger.error(`Validation failed (attempt ${attempt})`);
        if (attempt < config.retry.maxAttempts && config.retry.revertOnFailure) {
          logger.warn('Reverting and retrying...');
          await git.restore();
          continue;
        } else {
          logger.error('Max attempts reached - SKIPPING');
          if (config.retry.revertOnFailure) {
            await git.restore();
          }
          return false;
        }
      }
    }

    // Success!
    logger.success(`${chunk.id} completed successfully`);
    return true;
  }

  return false;
}

async function runMigration(
  chunk: Chunk,
  config: ComponentConfig,
  attempt: number
): Promise<boolean> {
  const stateDir = join('..', 'migrations', config.name, '.migration-state');
  mkdirSync(stateDir, { recursive: true });
  mkdirSync(join(stateDir, 'logs'), { recursive: true });

  // Build prompt
  const prompt = buildPrompt(chunk, config.promptTemplate);
  const promptFile = join(stateDir, `prompt-${chunk.id}-attempt${attempt}.txt`);
  writeFileSync(promptFile, prompt);

  // Build file list
  const files = [
    ...chunk.files.map(f => `../src/bundle/Resources/views/themes/admin/${f}`),
    config.rulesFile,
    promptFile
  ];

  // Run OpenCode
  const logFile = join(stateDir, 'logs', `${chunk.id}-attempt${attempt}.log`);
  const result = await runOpenCode({
    model: opencodeConfig.model,
    title: `${config.displayName} Migration: ${chunk.id}`,
    message: `Execute the ${config.name} migration according to the instructions in the attached prompt file. Follow all rules from the migration rules file. DO NOT run any bash commands - just edit the files.`,
    files
  }, logFile);

  return result.success;
}

async function validateChanges(chunk: Chunk, config: ComponentConfig): Promise<boolean> {
  const stateDir = join('..', 'migrations', config.name, '.migration-state');
  const logFile = join(stateDir, 'logs', `${chunk.id}-validation.log`);

  const result = await runValidation(
    config.validation.command,
    config.validation.timeout,
    logFile
  );

  if (result.success) {
    logger.success('Validation PASSED');
    return true;
  } else if (result.timedOut) {
    logger.error(`Validation TIMED OUT (${config.validation.timeout}s)`);
    return false;
  } else {
    logger.error(`Validation FAILED (exit code: ${result.exitCode})`);
    return false;
  }
}

async function createCommit(chunk: Chunk, config: ComponentConfig): Promise<void> {
  // Check if there are changes
  if (!(await git.hasUncommittedChanges())) {
    logger.warn('No changes to commit');
    return;
  }

  // Stage changes
  await git.add([]);

  // Build commit message
  let message = `${config.commit.prefix} ${chunk.id} - ${chunk.category}\n\n`;

  if (config.commit.includeFileList) {
    message += `Files migrated (${chunk.files.length}):\n`;
    chunk.files.forEach(f => {
      message += `- ${f}\n`;
    });
    message += '\n';
  }

  if (config.commit.includeStats) {
    message += `- Migrated legacy <button> to twig:ibexa:button component\n`;
    message += `- Buttons: ${chunk.buttonCount}\n`;
    message += `- Validation: PASSED\n\n`;
  }

  message += `See: ${config.rulesFile}`;

  await git.commit(message);

  const sha = await git.getCommitSha(true);
  logger.success(`Committed: ${sha}`);
}

async function saveProgress(session: MigrationSession, config: ComponentConfig): Promise<void> {
  const stateDir = join('..', 'migrations', config.name, '.migration-state');
  mkdirSync(stateDir, { recursive: true });

  const progressFile = join(stateDir, 'progress.txt');
  const content = `Last Updated: ${new Date().toISOString()}
Component: ${session.component}
Branch: ${session.migrationBranch}
Completed: ${session.completedChunks.length}/${session.totalChunks}
Failed: ${session.failedChunks.length}

Completed Chunks:
${session.completedChunks.map(id => `- ${id}`).join('\n')}

Failed Chunks:
${session.failedChunks.map(id => `- ${id}`).join('\n')}
`;

  writeFileSync(progressFile, content);
}

async function saveFailedChunk(chunk: Chunk, config: ComponentConfig): Promise<void> {
  const stateDir = join('..', 'migrations', config.name, '.migration-state');
  mkdirSync(join(stateDir, 'failed'), { recursive: true });

  const failedFile = join(stateDir, 'failed-chunks.txt');
  const existing = existsSync(failedFile) ? readFileSync(failedFile, 'utf-8') : '';
  const updated = existing + `${chunk.id}\n`;

  writeFileSync(failedFile, updated);
}

async function showStatus(config: ComponentConfig): Promise<void> {
  const stateDir = join('..', 'migrations', config.name, '.migration-state');
  const progressFile = join(stateDir, 'progress.txt');

  if (!existsSync(progressFile)) {
    logger.warn('No migration progress found');
    return;
  }

  const content = readFileSync(progressFile, 'utf-8');
  logger.header(`${config.displayName} Migration Status`);
  console.log(content);
}

// Run main
main();
