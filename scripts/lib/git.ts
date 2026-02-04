/**
 * Git operations wrapper
 */

import { execSync } from 'child_process';
import { GitStatus } from '../types.js';

export class Git {
  private cwd: string;

  constructor(cwd: string = process.cwd()) {
    this.cwd = cwd;
  }

  private exec(command: string): string {
    try {
      return execSync(command, {
        cwd: this.cwd,
        encoding: 'utf-8',
        stdio: ['pipe', 'pipe', 'pipe']
      }).trim();
    } catch (error: any) {
      throw new Error(`Git command failed: ${command}\n${error.message}`);
    }
  }

  async getCurrentBranch(): Promise<string> {
    return this.exec('git rev-parse --abbrev-ref HEAD');
  }

  async branchExists(name: string): Promise<boolean> {
    try {
      this.exec(`git rev-parse --verify ${name}`);
      return true;
    } catch {
      return false;
    }
  }

  async createBranch(name: string, from?: string): Promise<void> {
    const fromRef = from || 'HEAD';
    this.exec(`git checkout -b ${name} ${fromRef}`);
  }

  async checkout(branch: string): Promise<void> {
    this.exec(`git checkout ${branch}`);
  }

  async hasUncommittedChanges(): Promise<boolean> {
    const status = this.exec('git status --porcelain');
    return status.length > 0;
  }

  async restore(): Promise<void> {
    this.exec('git restore .');
    this.exec('git clean -fd');
  }

  async add(files: string[]): Promise<void> {
    if (files.length === 0) {
      this.exec('git add -A');
    } else {
      const fileList = files.map(f => `"${f}"`).join(' ');
      this.exec(`git add ${fileList}`);
    }
  }

  async commit(message: string): Promise<void> {
    // Escape quotes in commit message
    const escapedMessage = message.replace(/"/g, '\\"');
    this.exec(`git commit -m "${escapedMessage}"`);
  }

  async getCommitSha(short = true): Promise<string> {
    const format = short ? '--short' : '';
    return this.exec(`git rev-parse ${format} HEAD`);
  }

  async getStatus(): Promise<GitStatus> {
    const branch = await this.getCurrentBranch();
    const hasUncommitted = await this.hasUncommittedChanges();
    
    // Get ahead/behind count
    let ahead = 0;
    let behind = 0;
    
    try {
      const status = this.exec('git status -sb --porcelain');
      const match = status.match(/ahead (\d+)/);
      if (match) ahead = parseInt(match[1]);
      
      const behindMatch = status.match(/behind (\d+)/);
      if (behindMatch) behind = parseInt(behindMatch[1]);
    } catch {
      // Ignore errors (e.g., no upstream branch)
    }

    return { branch, hasUncommitted, ahead, behind };
  }

  async log(count = 10): Promise<string> {
    return this.exec(`git log --oneline -${count}`);
  }

  async diff(options = ''): Promise<string> {
    return this.exec(`git diff ${options}`);
  }
}

// Singleton instance
export const git = new Git();
