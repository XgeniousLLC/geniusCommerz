import puppeteer from 'puppeteer';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';
const __dirname = path.dirname(fileURLToPath(import.meta.url));

const PAGES = [
    {
        name: 'sourcing',
        template: 'file:///Users/sharifur/Downloads/eCommerce%20Dashboard/template/sourcing.html',
        live: 'http://poshivo.test/admin/sourcing',
    },
    {
        name: 'purchase-orders',
        template: 'file:///Users/sharifur/Downloads/eCommerce%20Dashboard/template/purchase-orders.html',
        live: 'http://poshivo.test/admin/accounting/purchases',
    },
    {
        name: 'ad-spend',
        template: 'file:///Users/sharifur/Downloads/eCommerce%20Dashboard/template/ad-spend.html',
        live: 'http://poshivo.test/admin/accounting/ad-spend',
    },
];

const OUT_DIR = path.resolve(__dirname, '../storage/screenshots');
if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

async function login(page) {
    await page.goto('http://poshivo.test/admin/login', { waitUntil: 'networkidle2' });
    const emailEl = await page.$('input[name="email"]');
    if (!emailEl) return;
    await page.type('input[name="email"]', 'admin@klixbd.com');
    await page.type('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }).catch(() => {}),
        page.click('button[type="submit"]'),
    ]);
    await new Promise(r => setTimeout(r, 800));
}

(async () => {
    const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });

    const lPage = await browser.newPage();
    await lPage.setViewport({ width: 1440, height: 900 });
    await login(lPage);

    for (const p of PAGES) {
        console.log(`\n[${p.name}] Screenshotting template...`);
        const tPage = await browser.newPage();
        await tPage.setViewport({ width: 1440, height: 900 });
        await tPage.goto(p.template, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        const tPath = path.join(OUT_DIR, `${p.name}-template.png`);
        await tPage.screenshot({ path: tPath, fullPage: true });
        await tPage.close();
        console.log(`  Template: ${tPath}`);

        console.log(`[${p.name}] Screenshotting live...`);
        await lPage.goto(p.live, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 1200));
        const lPath = path.join(OUT_DIR, `${p.name}-live.png`);
        await lPage.screenshot({ path: lPath, fullPage: true });
        console.log(`  Live: ${lPath}`);
    }

    await lPage.close();
    await browser.close();
    console.log('\nDone.');
})();
