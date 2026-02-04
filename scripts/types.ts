/**
 * Component Migration Tool - Type Definitions
 */

export interface ComponentConfig {
  name: string;
  displayName: string;
  planFile: string;
  rulesFile: string;
  promptTemplate: string;
  
  branch: {
    name: string;
    createIfMissing: boolean;
    switchAutomatically: boolean;
    mergeBack: boolean;  // Always false for isolated migrations
  };
  
  commit: {
    prefix: string;
    includeFileList: boolean;
    includeStats: boolean;
  };
  
  validation: {
    enabled: boolean;
    command: string;
    timeout: number;
  };
  
  retry: {
    maxAttempts: number;
    revertOnFailure: boolean;
  };
}

export interface Chunk {
  id: string;
  files: string[];
  category: string;
  buttonCount: number;
  hasUnmapped: boolean;
  notes?: string;
}

export interface MigrationSession {
  component: string;
  startTime: Date;
  baseBranch: string;
  migrationBranch: string;
  completedChunks: string[];
  failedChunks: string[];
  totalChunks: number;
}

export interface ValidationResult {
  success: boolean;
  exitCode: number;
  output: string;
  duration: number;
  timedOut: boolean;
}

export interface OpenCodeOptions {
  model: string;
  title: string;
  message: string;
  files: string[];
  timeout?: number;
}

export interface OpenCodeResult {
  success: boolean;
  exitCode: number;
  output: string;
  logFile: string;
}

export interface GitStatus {
  branch: string;
  hasUncommitted: boolean;
  ahead: number;
  behind: number;
}

export interface ProgressState {
  component: string;
  lastChunkId: string;
  completedChunks: string[];
  failedChunks: string[];
  lastUpdated: Date;
}
