import { defineConfig, devices } from '@playwright/test';
import path from 'path';

const artifactsDir = path.join(__dirname, 'artifacts');
const authFile = path.join(artifactsDir, 'auth.json');

export default defineConfig({
  testDir: './tests',
  outputDir: './artifacts/test-results',
  fullyParallel: false,
  workers: 2,
  retries: 1,
  timeout: 120_000,
  reporter: [
    ['list'],
  ],
  use: {
    baseURL: process.env.APP_URL ?? 'http://behatplaywright50.lh',
    storageState: authFile,
    locale: 'en-GB',
    headless: process.env.HEADLESS !== 'false',
    trace: 'retain-on-failure',
    viewport: { width: 1920, height: 1080 },
  },
  globalSetup: './global-setup.ts',
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
