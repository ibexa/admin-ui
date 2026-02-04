/**
 * OpenCode CLI wrapper
 */

import { spawn } from 'child_process';
import { writeFileSync } from 'fs';
import { OpenCodeOptions, OpenCodeResult } from '../types.js';
import { logger } from './logger.js';

export async function runOpenCode(options: OpenCodeOptions, logFile: string): Promise<OpenCodeResult> {
  logger.section(`OpenCode Migration: ${options.title}`);
  logger.info(`Model: ${options.model}`);
  logger.info(`Files: ${options.files.length}`);
  logger.blank();
  logger.warn('⏳ OpenCode is working (this may take 2-5 minutes)...');
  logger.dim(`Watch progress: tail -f ${logFile}`);
  logger.blank();
  
  const args = [
    'run',
    '-m', options.model,
    '--title', options.title,
    options.message,
    ...options.files.flatMap(f => ['-f', f])
  ];
  
  return new Promise((resolve) => {
    const proc = spawn('opencode', args, {
      cwd: '..',  // Run from parent directory
      stdio: ['ignore', 'pipe', 'pipe']
    });
    
    let output = '';
    
    proc.stdout?.on('data', (data) => {
      const text = data.toString();
      output += text;
      process.stdout.write(text);
    });
    
    proc.stderr?.on('data', (data) => {
      const text = data.toString();
      output += text;
      process.stderr.write(text);
    });
    
    proc.on('close', (code) => {
      // Save log
      writeFileSync(logFile, output);
      
      const result: OpenCodeResult = {
        success: code === 0,
        exitCode: code || 0,
        output,
        logFile
      };
      
      if (result.success) {
        logger.success('OpenCode completed successfully');
      } else {
        logger.error(`OpenCode failed with exit code ${code}`);
        logger.error(`Log: ${logFile}`);
      }
      
      resolve(result);
    });
  });
}
