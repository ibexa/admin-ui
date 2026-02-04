/**
 * Colored console logger with progress indicators
 */

import chalk from 'chalk';

export class Logger {
  private verbose: boolean;

  constructor(verbose = true) {
    this.verbose = verbose;
  }

  info(message: string): void {
    console.log(chalk.cyan(`ℹ ${message}`));
  }

  success(message: string): void {
    console.log(chalk.green(`✓ ${message}`));
  }

  warn(message: string): void {
    console.log(chalk.yellow(`⚠ ${message}`));
  }

  error(message: string): void {
    console.error(chalk.red(`✗ ${message}`));
  }

  debug(message: string): void {
    if (this.verbose) {
      console.log(chalk.gray(`🔍 ${message}`));
    }
  }

  header(message: string): void {
    const line = '═'.repeat(50);
    console.log(chalk.blue(`\n${line}`));
    console.log(chalk.blue(`  ${message}`));
    console.log(chalk.blue(`${line}\n`));
  }

  section(message: string): void {
    const line = '━'.repeat(50);
    console.log(chalk.cyan(`\n${line}`));
    console.log(chalk.cyan(`  ${message}`));
    console.log(chalk.cyan(`${line}\n`));
  }

  progress(current: number, total: number, message: string): void {
    const percent = Math.floor((current / total) * 100);
    const filled = Math.floor((percent / 100) * 40);
    const empty = 40 - filled;
    
    const bar = chalk.green('█'.repeat(filled)) + chalk.gray('░'.repeat(empty));
    const stats = chalk.white(`${current}/${total} (${percent}%)`);
    
    process.stdout.write(`\r[${bar}] ${stats} ${chalk.cyan(message)}`);
    
    if (current === total) {
      console.log(); // New line when complete
    }
  }

  blank(): void {
    console.log();
  }

  dim(message: string): void {
    console.log(chalk.gray(message));
  }

  bold(message: string): void {
    console.log(chalk.bold(message));
  }

  table(headers: string[], rows: string[][]): void {
    const colWidths = headers.map((h, i) => {
      const maxContentWidth = Math.max(
        h.length,
        ...rows.map(r => (r[i] || '').length)
      );
      return maxContentWidth + 2;
    });

    // Header
    const headerStr = headers.map((h, i) => h.padEnd(colWidths[i])).join(' ');
    console.log(chalk.bold(headerStr));
    console.log('─'.repeat(headerStr.length));

    // Rows
    rows.forEach(row => {
      const rowStr = row.map((cell, i) => cell.padEnd(colWidths[i])).join(' ');
      console.log(rowStr);
    });
  }
}

// Singleton instance
export const logger = new Logger();
