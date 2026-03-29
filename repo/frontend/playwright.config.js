import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 120000,
  fullyParallel: false,
  workers: 1,
  use: {
    baseURL: process.env.E2E_WEB_URL || 'http://localhost:3000',
    trace: 'on-first-retry',
  },
})
