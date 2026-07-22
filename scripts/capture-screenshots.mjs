import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

await mkdir('docs/screenshots', { recursive: true });
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
await page.goto('http://127.0.0.1:8088', { waitUntil: 'networkidle' });
await page.screenshot({ path: 'docs/screenshots/home.png', fullPage: true });
await page.goto('http://127.0.0.1:8088/products/crystal-pearl-necklace', { waitUntil: 'networkidle' });
await page.screenshot({ path: 'docs/screenshots/product.png', fullPage: true });
await page.locator('button.buy').click();
await page.waitForSelector('.cart-drawer.open');
await page.waitForTimeout(500);
await page.screenshot({ path: 'docs/screenshots/cart-drawer.png', fullPage: false });
await page.goto('http://127.0.0.1:8088/checkout', { waitUntil: 'networkidle' });
await page.screenshot({ path: 'docs/screenshots/checkout.png', fullPage: true });
await browser.close();
