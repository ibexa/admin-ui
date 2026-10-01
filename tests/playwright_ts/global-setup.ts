import { chromium, FullConfig } from '@playwright/test';
import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';

export default async function globalSetup(config: FullConfig): Promise<void> {
  const projectRoot = path.resolve(__dirname, '../../../../../');
  const migrationFile = path.resolve(__dirname, 'migrations/setup.yaml');
  const dockerEnvFile = process.env.DOCKER_COMPOSE_ENV_FILE;

  if (fs.existsSync(migrationFile)) {
    try {
      if (dockerEnvFile) {
        const containerMigrationFile = migrationFile.replace(projectRoot, '/var/www');
        execSync(
          `docker compose --env-file="${dockerEnvFile}" exec -T --user www-data app sh -c "php bin/console ibexa:migrations:import '${containerMigrationFile}' --no-interaction && php bin/console ibexa:migrations:migrate --no-interaction"`,
          { cwd: projectRoot, stdio: 'inherit' },
        );
      } else {
        execSync(
          `php bin/console ibexa:migrations:import "${migrationFile}" --no-interaction && php bin/console ibexa:migrations:migrate --no-interaction`,
          { cwd: projectRoot, stdio: 'inherit' },
        );
      }
    } catch {
      // migration already executed or non-fatal error — continue
    }
  }

  const baseURL = process.env.APP_URL ?? 'http://behatplaywright50.lh';
  const adminUser = process.env.ADMIN_USER ?? 'admin';
  const adminPassword = process.env.ADMIN_PASSWORD ?? 'publish';
  const headless = process.env.HEADLESS === 'true';
  const sessionMaxAge = parseInt(process.env.SESSION_MAX_AGE ?? '3600', 10);

  const artifactsDir = path.join(__dirname, 'artifacts');
  const authFile = path.join(artifactsDir, 'auth.json');
  const loginErrorFile = path.join(artifactsDir, 'login-error.txt');

  if (!fs.existsSync(artifactsDir)) {
    fs.mkdirSync(artifactsDir, { recursive: true });
  }

  if (fs.existsSync(authFile)) {
    if (sessionMaxAge > 0) {
      const stat = fs.statSync(authFile);
      const ageSeconds = (Date.now() - stat.mtimeMs) / 1000;
      if (ageSeconds > sessionMaxAge) {
        fs.unlinkSync(authFile);
      }
    }
  }

  if (fs.existsSync(authFile)) {
    return;
  }

  if (fs.existsSync(loginErrorFile)) {
    fs.unlinkSync(loginErrorFile);
  }

  const browser = await chromium.launch({ headless });
  const context = await browser.newContext({
    baseURL,
    locale: 'en-GB',
  });
  const page = await context.newPage();

  try {
    await page.goto(`${baseURL}/admin/login`);
    await page.waitForLoadState('domcontentloaded');
    await page.locator('#username').waitFor({ state: 'visible', timeout: 30_000 });
    await page.locator('#username').pressSequentially(adminUser);
    await page.keyboard.press('Tab');
    await page.locator('#password').pressSequentially(adminPassword);
    await page.keyboard.press('Tab');
    await page.locator('button[type="submit"].ibexa-login__btn--sign-in').waitFor({ state: 'visible', timeout: 10_000 });
    await page.locator('button[type="submit"].ibexa-login__btn--sign-in').click();
    await page.waitForLoadState('networkidle');

    await context.storageState({ path: authFile });
  } catch (error) {
    const message = error instanceof Error
      ? error.message + '\n' + error.stack
      : String(error);
    fs.writeFileSync(loginErrorFile, message);
    console.error('Login failed:', message);
  } finally {
    await context.close();
    await browser.close();
  }
}
