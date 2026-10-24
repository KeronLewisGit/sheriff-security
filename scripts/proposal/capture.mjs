// Captures section-aware screenshots of every screen (desktop + phone) for the client proposal.
// Slices follow the page's own sections so a screenshot never breaks mid-block.
// Usage: npm run proposal   (env: BASE_URL, ADMIN_EMAIL/ADMIN_PASSWORD, PACK_URL, TICKET_REF/TICKET_EMAIL, PHP_BIN)
// Uses the dev server that is already running (php artisan serve --port=8104) and its demo data.
import puppeteer from 'puppeteer-core';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8104';
const OUT = path.resolve('dist/proposal/shots');
const CHROME = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PHP = process.env.PHP_BIN ?? (fs.existsSync('/opt/homebrew/opt/php@8.3/bin/php') ? '/opt/homebrew/opt/php@8.3/bin/php' : 'php');
const ADMIN = { email: process.env.ADMIN_EMAIL ?? 'admin@sheriff.test', password: process.env.ADMIN_PASSWORD ?? 'password' };
const TICKET = { ref: process.env.TICKET_REF ?? 'SA-26-00002', email: process.env.TICKET_EMAIL ?? 'customer2@example.com' };

/** A live signed vendor-pack link for the first approved demo request (read-only: nothing is written). */
function packUrl() {
  if (process.env.PACK_URL) return process.env.PACK_URL;
  const out = execFileSync(PHP, ['artisan', 'tinker', '--execute',
    "echo App\\Models\\VendorRequest::where('status', 'approved')->where('expires_at', '>', now())->orderBy('id')->first()?->packUrl();"], { encoding: 'utf8' });
  const url = out.trim().split('\n').pop();
  if (!url.startsWith('http')) throw new Error('No live vendor pack link. Approve a demo vendor request first, or set PACK_URL.');
  return url;
}
const PACK = packUrl();

fs.mkdirSync(OUT, { recursive: true });

const PUBLIC = [
  ['home', '/'], ['services', '/services'], ['service-oil', '/services/oil-and-gas'], ['industries', '/industries'],
  ['hse', '/hse-and-compliance'], ['group', '/group-companies'], ['leadership', '/leadership'],
  ['rfp', '/request-a-proposal'], ['vendor', '/vendor-prequalification'], ['alarm', '/alarm-service'],
  ['careers', '/careers'], ['job', '/careers/security-officer'], ['apply', '/careers/apply'], ['contact', '/contact'],
];
// Pages a client or customer reaches through a private link or a lookup, not the menu.
const PORTAL = [
  ['vendor-pack', PACK, { absolute: true }],
  ['vendor-expired', '/vendor-pack/link-from-an-old-email'],
  ['alarm-status', '/alarm-service?tab=status', { lookup: true }],
];
const ADMIN_PAGES = [
  ['admin-dashboard', '/admin'], ['admin-board', '/admin/proposals/board', { fullPage: false }], ['admin-proposals', '/admin/proposals'],
  ['admin-proposal', '/admin/proposals/1'], ['admin-vendor', '/admin/vendor-requests'], ['admin-vendor-pending', '/admin/vendor-requests/1'],
  ['admin-vendor-approved', '/admin/vendor-requests/3'], ['admin-compliance', '/admin/compliance'], ['admin-tickets', '/admin/alarm-tickets'],
  ['admin-ticket', '/admin/alarm-tickets/2'], ['admin-pipeline', '/admin/applications/pipeline', { fullPage: false }], ['admin-applications', '/admin/applications'],
  ['admin-applicant', '/admin/applications/1'], ['admin-jobs', '/admin/jobs'], ['admin-messages', '/admin/messages'],
  ['admin-users', '/admin/users'], ['admin-settings', '/admin/settings'],
];
const MOBILE = [
  ['m-home', '/'], ['m-vendor', '/vendor-prequalification'], ['m-rfp', '/request-a-proposal'],
  ['m-vendor-pack', PACK, { absolute: true }], ['m-admin-dashboard', '/admin'], ['m-admin-vendor', '/admin/vendor-requests/1'],
];

const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', protocolTimeout: 60000, args: ['--hide-scrollbars', '--no-sandbox'] });
const page = await browser.newPage();
await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }, { name: 'prefers-color-scheme', value: 'light' }]);

async function settle() {
  await page.evaluate(async () => {
    document.querySelectorAll('[data-reveal]').forEach(e => e.classList.add('is-visible'));
    // Lazy photos below the fold would otherwise be captured as empty boxes.
    document.querySelectorAll('img[loading="lazy"]').forEach(i => { i.loading = 'eager'; });
    // trigger lazy images
    window.scrollTo(0, document.body.scrollHeight); await new Promise(r => setTimeout(r, 400)); window.scrollTo(0, 0);
    await Promise.race([
      Promise.all([...document.images].map(i => (i.complete ? Promise.resolve() : new Promise(r => { i.onload = i.onerror = r; })).then(() => i.decode?.().catch(() => {})))),
      new Promise(r => setTimeout(r, 5000)),
    ]);
  });
  await new Promise(r => setTimeout(r, 500));
}

/**
 * Measure natural content blocks (header, each section, footer). Blocks taller than the
 * page budget are replaced by their children, recursively, so slices follow real section
 * boundaries instead of cutting wherever the pixel budget runs out.
 */
async function measureBlocks(maxH) {
  return page.evaluate((maxH) => {
    const rect = (el) => { const r = el.getBoundingClientRect(); return { top: Math.round(r.top + window.scrollY), bottom: Math.round(r.bottom + window.scrollY) }; };
    const visible = (el) => { const cs = getComputedStyle(el); const r = el.getBoundingClientRect(); return cs.display !== 'none' && cs.visibility !== 'hidden' && r.height > 24 && cs.position !== 'fixed'; };
    const skip = (el) => el.matches('script, style, template, [x-cloak], [aria-hidden="true"]:not(section)');
    const label = (el) => {
      const hs = [...el.querySelectorAll('h1, h2, h3')].filter(h => visible(h) && h.closest('nav, footer, [aria-hidden="true"]') === null);
      const seen = new Set();
      const text = (h) => { const c = h.cloneNode(true); c.querySelectorAll('[aria-hidden="true"], template').forEach(e => e.remove()); return c.textContent.replace(/\s+/g, ' ').trim(); };
      return hs.map(text).filter(t => t && !seen.has(t.toLowerCase()) && seen.add(t.toLowerCase())).slice(0, 4);
    };
    const explode = (el) => {
      const r = { ...rect(el), labels: label(el) };
      if (r.bottom - r.top <= maxH) return [r];
      const kids = [...el.children].filter(k => !skip(k) && visible(k));
      if (kids.length === 0) return [{ top: r.top, bottom: r.bottom, labels: r.labels }];
      if (kids.length === 1) return explode(kids[0]); // wrapper div – look inside it
      return kids.flatMap(explode);
    };
    // Admin layout: the content column is <main id="content"> beside the sidebar; public layout: body-level banner/header/main/footer.
    const adminMain = document.querySelector('body.admin main#content');
    const roots = adminMain
      ? [...adminMain.children].map(el => ({ el, trailing: false }))
      : [...document.body.children].filter(el => ['HEADER', 'MAIN', 'FOOTER', 'DIV', 'SECTION'].includes(el.tagName) && !skip(el) && visible(el))
          .flatMap(el => el.tagName === 'MAIN' ? [...el.children].map(k => ({ el: k, trailing: false })) : [{ el, trailing: el.tagName === 'FOOTER' || (el.tagName === 'SECTION' && el.previousElementSibling?.tagName === 'MAIN') }]);
    const blocks = roots.filter(r => visible(r.el)).flatMap(r => explode(r.el).map(b => ({ ...b, trailing: r.trailing }))).filter(b => b.bottom > b.top).sort((a, b) => a.top - b.top);
    // merge overlaps / fill gaps so nothing is lost between blocks
    const merged = [];
    for (const b of blocks) {
      const last = merged[merged.length - 1];
      if (last && b.top < last.bottom - 2) { last.bottom = Math.max(last.bottom, b.bottom); last.trailing = last.trailing && b.trailing; last.labels = [...new Set([...last.labels, ...b.labels])]; } else merged.push({ ...b, labels: [...b.labels] }); // merge true overlaps only
    }
    const docH = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
    if (merged.length) { merged[0].top = 0; merged[merged.length - 1].bottom = Math.max(merged[merged.length - 1].bottom, docH); for (let i = 1; i < merged.length; i++) merged[i].top = merged[i - 1].bottom; }
    return { blocks: merged, docH, docW: document.documentElement.clientWidth };
  }, maxH);
}

/** Greedily pack consecutive blocks into page-sized groups; a lone oversize block is hard-cut as a last resort. */
function packBlocks(blocks, maxH, docH) {
  const groups = [];
  let cur = null;
  for (const b of blocks) {
    const h = b.bottom - b.top;
    if (cur && b.bottom - cur.top <= maxH) { cur.bottom = b.bottom; cur.labels = [...new Set([...cur.labels, ...(b.labels ?? [])])]; continue; }
    if (cur) groups.push(cur);
    if (h > maxH) {
      for (let y = b.top; y < b.bottom; y += maxH) groups.push({ top: y, bottom: Math.min(y + maxH, b.bottom), labels: b.labels ?? [] });
      cur = null;
    } else cur = { top: b.top, bottom: b.bottom, labels: [...(b.labels ?? [])] };
  }
  if (cur) groups.push(cur);
  // Fold a short sliver (a filter bar, a thin footer strip) into its neighbour when the pair still
  // fits a page once scaled, so no page carries a 150px strip on its own.
  for (let i = 0; i < groups.length && groups.length > 1; i++) {
    const g = groups[i], h = g.bottom - g.top;
    if (h >= maxH * 0.25) continue;
    const next = groups[i + 1], prev = groups[i - 1];
    const join = (a, b) => { a.bottom = b.bottom; a.labels = [...new Set([...a.labels, ...b.labels])]; };
    if (next && next.bottom - g.top <= maxH * 1.35) { join(g, next); groups.splice(i + 1, 1); i--; }
    else if (prev && g.bottom - prev.top <= maxH * 1.35) { join(prev, g); groups.splice(i, 1); i--; }
  }
  groups[groups.length - 1].bottom = Math.min(groups[groups.length - 1].bottom, docH);
  return groups;
}

const RATIO = 1.08; // slice height ÷ width that fits a letter page beneath the heading and bullets

async function shoot(name, url, { width = 1440, height = 900, fullPage = true, mobile = false, absolute = false, lookup = false } = {}) {
  await page.setViewport({ width, height, deviceScaleFactor: mobile ? 2 : 1.25, isMobile: mobile, hasTouch: mobile });
  await page.goto(absolute ? url : BASE + url, { waitUntil: 'networkidle0' });
  if (lookup) { // the alarm-ticket status page is the answer to a POSTed lookup
    await page.type('#lookup-reference', TICKET.ref);
    await page.type('#lookup-email', TICKET.email);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('form#status button')]);
  }
  await settle();
  const files = []; const labels = [];
  if (!fullPage) {
    const file = path.join(OUT, `${name}.jpg`);
    await page.screenshot({ path: file, type: 'jpeg', quality: 70 });
    files.push(file);
    console.log(`✓ ${name} viewport`);
    return { name, url: absolute ? new URL(url).pathname.replace(/[^/]{20,}$/, '…') : url, chunks: files.map(f => path.resolve(f)), labels: [[]], mobile };
  }
  const maxH = Math.round(width * RATIO);
  let { blocks, docH } = await measureBlocks(maxH);
  if (name !== 'home') { blocks = blocks.filter(b => !b.trailing); docH = blocks[blocks.length - 1].bottom; } // CTA band + footer are shown once (home)
  const groups = packBlocks(blocks, maxH, docH);
  for (const [i, g] of groups.entries()) {
    const file = path.join(OUT, groups.length > 1 ? `${name}-${i}.jpg` : `${name}.jpg`);
    await page.screenshot({ path: file, type: 'jpeg', quality: 70, captureBeyondViewport: true, clip: { x: 0, y: g.top, width, height: g.bottom - g.top } });
    files.push(file); labels.push(g.labels ?? []);
  }
  console.log(`✓ ${name} → ${groups.length} slice(s): ${groups.map(g => (g.labels ?? []).join(' | ') || '(no heading)').join('  ·  ')}`);
  return { name, url: absolute ? new URL(url).pathname.replace(/[^/]{20,}$/, '…') : url, chunks: files.map(f => path.resolve(f)), labels, w: width, h: docH };
}

async function login(url, email, password) {
  await page.goto(BASE + url, { waitUntil: 'networkidle0' });
  await page.type('input[name=email]', email);
  await page.type('input[name=password]', password);
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button[type=submit], form button')]);
}

const shots = [];
for (const [n, u] of PUBLIC) shots.push(await shoot(n, u));
for (const [n, u, o] of PORTAL) shots.push(await shoot(n, u, o));
await login('/login', ADMIN.email, ADMIN.password);
for (const [n, u, o] of ADMIN_PAGES) shots.push(await shoot(n, u, o));
for (const [n, u, o] of MOBILE) shots.push(await shoot(n, u, { width: 390, height: 844, mobile: true, fullPage: false, ...o }));
fs.writeFileSync(path.join(OUT, 'chunks.json'), JSON.stringify(Object.fromEntries(shots.map(s => [s.name, s])), null, 1));
await browser.close();
console.log('done');
