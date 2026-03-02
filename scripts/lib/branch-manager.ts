/**
 * Branch isolation manager
 */

import { ComponentConfig, MigrationSession } from '../types.js';
import { git } from './git.js';
import { logger } from './logger.js';

export class BranchManager {
  constructor(private config: ComponentConfig) {}

  async startMigration(): Promise<MigrationSession> {
    const baseBranch = await git.getCurrentBranch();
    const migrationBranch = this.config.branch.name;

    logger.info(`Starting migration on branch: ${migrationBranch}`);
    logger.info(`Base branch: ${baseBranch} (will NOT be merged back)`);
    logger.blank();

    // Check if migration branch exists
    const exists = await git.branchExists(migrationBranch);

    if (!exists && this.config.branch.createIfMissing) {
      logger.info(`Creating new migration branch: ${migrationBranch}`);
      await git.createBranch(migrationBranch, baseBranch);
    } else if (exists) {
      logger.info(`Switching to existing branch: ${migrationBranch}`);
      await git.checkout(migrationBranch);
    }

    // Verify we're on the right branch
    const currentBranch = await git.getCurrentBranch();
    if (currentBranch !== migrationBranch) {
      throw new Error(`Failed to switch to ${migrationBranch}`);
    }

    logger.success(`Now on branch: ${currentBranch}`);
    logger.blank();

    return {
      component: this.config.name,
      startTime: new Date(),
      baseBranch,
      migrationBranch,
      completedChunks: [],
      failedChunks: [],
      totalChunks: 0
    };
  }

  async finishMigration(session: MigrationSession): Promise<void> {
    logger.blank();
    logger.header('Migration Complete!');

    logger.info(`Branch: ${session.migrationBranch}`);
    logger.success(`Completed: ${session.completedChunks.length} chunks`);
    
    if (session.failedChunks.length > 0) {
      logger.error(`Failed: ${session.failedChunks.length} chunks`);
      logger.blank();
      logger.warn('Failed chunks:');
      session.failedChunks.forEach(id => logger.error(`  - ${id}`));
    }

    logger.blank();
    logger.warn('⚠️  Migration branch is ISOLATED - not merged back');
    logger.info(`   Migrations stay on: ${session.migrationBranch}`);
    logger.info(`   Tools stay on: ${session.baseBranch}`);

    logger.blank();
    logger.bold('Next steps:');
    logger.info(`  1. Review changes: git log --oneline ${session.migrationBranch}`);
    logger.info(`  2. Test manually on: ${session.migrationBranch}`);
    logger.info(`  3. Switch back: git checkout ${session.baseBranch}`);
    logger.blank();

    if (this.config.branch.mergeBack) {
      logger.error('⚠️  Config has mergeBack=true but this should be false!');
    }
  }

  async ensureOnMigrationBranch(): Promise<void> {
    const current = await git.getCurrentBranch();
    if (current !== this.config.branch.name) {
      throw new Error(
        `Not on migration branch!\n` +
        `  Current: ${current}\n` +
        `  Expected: ${this.config.branch.name}\n` +
        `  Run: git checkout ${this.config.branch.name}`
      );
    }
  }
}
