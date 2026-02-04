/**
 * Run frontend validation (yarn test)
 */

import { spawn } from 'child_process';
import { writeFileSync } from 'fs';
import { ValidationResult } from '../types.js';
import { logger } from './logger.js';

export async function runValidation(
  command: string,
  timeout: number,
  logFile: string
): Promise<ValidationResult> {
  const startTime = Date.now();
  
  logger.section('Running Validation: yarn test');
  logger.info(`Command: ${command}`);
  logger.info(`Timeout: ${timeout}s`);
  logger.blank();
  
  return new Promise((resolve) => {
    const proc = spawn(command, {
      shell: true,
      cwd: '..',  // Run from parent directory
      stdio: ['ignore', 'pipe', 'pipe']
    });
    
    let output = '';
    let timedOut = false;
    
    const timer = setTimeout(() => {
      timedOut = true;
      proc.kill();
    }, timeout * 1000);
    
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
      clearTimeout(timer);
      const duration = Date.now() - startTime;
      
      // Save log
      writeFileSync(logFile, output);
      
      const result: ValidationResult = {
        success: code === 0 && !timedOut,
        exitCode: code || (timedOut ? 124 : 0),
        output,
        duration,
        timedOut
      };
      
      resolve(result);
    });
  });
}
