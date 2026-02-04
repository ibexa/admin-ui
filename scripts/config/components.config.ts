/**
 * Component migration configurations
 */

import { ComponentConfig } from '../types.js';

export const buttonConfig: ComponentConfig = {
  name: 'button',
  displayName: 'Button',
  planFile: '../migrations/button/MIGRATION_PLAN.txt',
  rulesFile: '../migrations/button/MIGRATION_RULES.md',
  promptTemplate: '../migrations/button/prompts/migrate-chunk.txt',
  
  branch: {
    name: 'migrate/buttons',
    createIfMissing: true,
    switchAutomatically: true,
    mergeBack: false  // CRITICAL: Never merge back - keep migrations isolated
  },
  
  commit: {
    prefix: 'Migrate buttons:',
    includeFileList: true,
    includeStats: true
  },
  
  validation: {
    enabled: true,
    command: 'yarn test',
    timeout: 300  // 5 minutes
  },
  
  retry: {
    maxAttempts: 3,
    revertOnFailure: true
  }
};

// Future: Badge configuration
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
    mergeBack: false  // Same pattern - never merge back
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

// Registry of all available components
const componentRegistry: Record<string, ComponentConfig> = {
  button: buttonConfig,
  badge: badgeConfig
};

export function getComponentConfig(name: string): ComponentConfig {
  const config = componentRegistry[name];
  if (!config) {
    throw new Error(
      `Unknown component: ${name}\n` +
      `Available: ${Object.keys(componentRegistry).join(', ')}`
    );
  }
  return config;
}

export function listComponents(): string[] {
  return Object.keys(componentRegistry);
}
