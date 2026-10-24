// Builds the client proposal (letter-size PDF) from the captured screenshots, in Sheriff's
// own palette and the site's self-hosted fonts. Usage: npm run proposal (or node scripts/proposal/build.mjs
// after capture.mjs). Every client fact below comes from config/sheriff.php and its cited sources.
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const CHROME = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const SHOTS = path.resolve('dist/proposal/shots');
const chunks = JSON.parse(fs.readFileSync(path.join(SHOTS, 'chunks.json'), 'utf8'));
const OUT_HTML = path.resolve('dist/proposal/proposal.html');
const OUT_PDF = path.resolve('dist/proposal/Sheriff-Security-Website-Proposal.pdf');
const DATE = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
const TESTS = process.env.TEST_COUNT ?? '76';
const img = (f) => 'file://' + f;
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

/* Self-hosted site fonts from the Vite build (hashed file names). */
const ASSETS = path.resolve('public/build/assets');
const font = (prefix) => {
  const f = fs.readdirSync(ASSETS).find((n) => n.startsWith(prefix) && n.endsWith('.woff2'));
  if (!f) throw new Error(`Font ${prefix} not found in public/build/assets; run npm run build first.`);
  return 'file://' + path.join(ASSETS, f);
};
const FONTS = [
  ['Chakra Petch', 600, 'chakra-petch-600-normal'], ['Chakra Petch', 700, 'chakra-petch-700-normal'],
  ['Red Hat Text', 400, 'red-hat-text-400-normal'], ['Red Hat Text', 500, 'red-hat-text-500-normal'], ['Red Hat Text', 700, 'red-hat-text-700-normal'],
  ['Red Hat Mono', 500, 'red-hat-mono-500-normal'],
].map(([family, weight, prefix]) => `@font-face { font-family: "${family}"; font-weight: ${weight}; font-style: normal; src: url("${font(prefix)}") format("woff2"); }`).join('\n');

/* Sheriff's own logo (public/img/brand, the same files the site uses): on-dark for the cover, on-light for the running footer. */
const LOGO_FILE = (v) => 'file://' + path.resolve(`public/img/brand/sheriff-logo-${v}.svg`);
const LOGO = (h, v = 'on-dark') => `<img class="logo" src="${LOGO_FILE(v)}" alt="Sheriff Security Service" height="${h}" style="height:${h}px;width:auto">`;

/** Hand-written captions per slice (key = screen name, index = slice). Written from what each slice shows. */
const CAPTIONS = {
  home: [
    'Hero over an illustrative offshore photograph: the positioning line, OilNOW’s “prototype for oil and gas security in Guyana” with its source, the Compliance-at-a-glance card (ISO 9001, ISO 45001, Local Content, ISO/IEC 27001 in progress, GAPSO 2024) and the three ways in: request a proposal, get the vendor pack, report an alarm fault.',
    'The six lines of business as permit-to-work tag cards (PTW-01 to PTW-06), each linking to its own scope page.',
    '“When procurement Googles you first”: the energy-sector pitch with the points an operator’s supplier team checks, cited to OilNOW and Stabroek News.',
    'Marine and air patrol on the indigo chart-grid band. Photo labels mark every image as illustrative (Wikimedia Commons).',
    'GAPSO 2024: 13 of 26 accolades with the named company awards, then the two certified management systems and the CEO’s quote on the workforce insurance plan, each with its source.',
    'Careers call-to-action showing the live count of open positions, and the footer with services, procurement links and head-office details (the address and phone from Sheriff’s own site, editable in the back office).',
  ],
  services: [
    'Services hero and the six service cards. Each card states only what a cited source says Sheriff does.',
    'Support services named on Sheriff’s own 2021 site (risk assessment, command centre, GPS fleet tracking, training academy), then the site-wide request-for-proposal band.',
  ],
  'service-oil': [
    'A service page (oil & gas and energy): what the scope covers, sources, a “Quote this scope” card that pre-fills the request-for-proposal form, and how a contract starts (request, site survey, proposal and mobilisation).',
    'Other services, so a visitor can move across scopes without returning to the menu.',
  ],
  industries: [
    'Industries hero; sectors are described without naming clients (“references are shared privately during prequalification”).',
    'Banking, commercial, diplomatic and residential sectors, each with the services that apply.',
    'Photo note and the request-for-proposal band.',
  ],
  hse: [
    'HSE & compliance: the certified management systems and Local Content registration, with ISO/IEC 27001 shown honestly as “being pursued (2023)”.',
    'The vendor pack explained in four steps on the indigo band, the Sheriff Training Academy topics and the workforce health and life insurance plan.',
    'GAPSO 2024 company and leadership awards, and an “In the press” list linking to every article the site relies on.',
  ],
  group: ['The Sheriff group: six companies as tag cards, each with its source (LinkedIn, Stabroek News, the 2021 site), plus head office and the announced Palmyra headquarters.'],
  leadership: ['Leadership as last reported in the press, with the source under each name. Portraits are left for Sheriff’s own photography.'],
  rfp: [
    'Request for proposal: the promise up front (“Response target: 24 hours from a complete request”).',
    'Organisation and site details: industry, type of site (onshore, shorebase, offshore support, port, office, several sites) and location, beside “What happens next” and a link to the vendor pack.',
    'Posts and coverage, armed or unarmed, start date, contract term and up to five scope or tender documents (stored privately).',
  ],
  vendor: [
    'Vendor prequalification pack, step 1: the procurement team ticks the compliance documents it needs. The list comes from the compliance library in the back office.',
    'Steps 2 and 3: who is asking and why (tender, registration or audit, with the tender reference), and a confirmation that the documents are for procurement use only.',
  ],
  alarm: [
    'Alarm service & fault report for Sheriff Alarms customers, with the emergency notice on top: call 911, then Sheriff, and do not use the form.',
    'Report a fault or check a ticket on one page. The report asks what is happening, whether the site is unprotected (urgent), the alarm account and site, and gives a ticket number.',
  ],
  careers: ['Careers: sourced benefits (workforce insurance, training academy, GAPSO awards), the open positions pulled live from the back office, and the documents to have ready: police clearance, ID, certificates and an optional CV.'],
  job: ['A job posting with duties, requirements, location, closing date and an Apply card.'],
  apply: [
    'Apply online: position, personal details and region.',
    'Experience, firearm licence and rotation availability, then private uploads (police clearance, ID, up to four certificates, CV) and consent.',
  ],
  contact: ['Contact: shortcuts to the dedicated forms (proposal, vendor pack, alarm fault, jobs), the editable head-office details and a general message form.'],
  'vendor-pack': [
    'The private vendor pack a procurement officer opens from the emailed link: the company and reference, and the reminder that each download is logged.',
    'Expiry date and time (Guyana time) and a download card for every approved document. Sample documents shown.',
  ],
  'vendor-expired': ['What anyone sees after a link expires, is edited or is withdrawn: a plain explanation and a button to request the pack again.'],
  'alarm-status': ['Ticket status after a customer enters the ticket number and email: progress steps, technician visit, site and the note staff chose to share. Internal notes never appear. Demo ticket.'],
  'admin-dashboard': [
    'The dashboard leads with the number to act on: proposal requests not answered within the 24-hour target, oldest first. Below are counts for new proposals, vendor packs awaiting approval, open alarm tickets and messages, plus the weekly request chart and the proposal pipeline. Sample data.',
    'HR’s part of the dashboard: applications waiting more than two days, the recruitment pipeline and the latest applications.',
  ],
  'admin-board': ['Proposal board: drag a card from New to Qualified, Site survey, Proposal sent, Won or Lost. Leaving “New” records the first response; an orange edge marks requests past the target. Sample data.'],
  'admin-proposals': ['Proposal list with status filters, an overdue filter, search and the industry filter; overdue requests carry an orange tag. Sample data.'],
  'admin-proposal': ['One request: contact, site, posts, coverage and start date, attached documents, and the pipeline panel for stage, owner, estimated monthly value (GYD) and internal notes. Sample data.'],
  'admin-vendor': ['Vendor pack requests by status (awaiting approval, approved, declined, revoked), with document and download counts and the link’s expiry. Sample data.'],
  'admin-vendor-pending': ['A pending request: who is asking, for which tender, and which documents. Approve emails the signed link; Decline records an internal reason and sends nothing. Sample data.'],
  'admin-vendor-approved': ['An approved request: the live link and its expiry, a one-click Withdraw, and the download log with time, document, IP address and browser. Sample data.'],
  'admin-compliance': ['The compliance library behind the vendor pack: category, private file, expiry (the insurance certificate is flagged as expiring) and how often each was downloaded. Sample “SAMPLE” PDFs.'],
  'admin-tickets': ['Alarm tickets: open tickets with urgent faults first, status filters and the technician visit date. Sample data.'],
  'admin-ticket': ['One ticket: the customer’s report and the update panel for status, technician visit, the note shown to the customer and internal notes. Sample data.'],
  'admin-pipeline': ['Recruitment pipeline: New, Screening, Interview and Training columns; each card shows region, police clearance received and firearm licence. Sample applicants.'],
  'admin-applications': ['Applications with status tabs, position and region filters, document check (PCC, ID, files) and CSV export. Sample applicants.'],
  'admin-applicant': ['An applicant: answers from the form, private document downloads for HR only, and the decision panel with HR notes. Sample applicant.'],
  'admin-jobs': ['Job postings with department, status, openings, application counts and closing dates.'],
  'admin-messages': ['Messages from the contact form, marked handled when dealt with. Sample messages.'],
  'admin-users': ['Users & roles: administrator, business development and human resources accounts, each seeing only its part of the dashboard.'],
  'admin-settings': ['Settings: the phone, emails, address, hours and social links shown on the website, the proposal response target, the vendor-link lifetime and who receives alerts. All editable without a developer.'],
};

const SECTIONS = [
  { id: 'public', eyebrow: 'Part 1', title: 'The public website', intro: 'A complete company website where today there is none. Every claim on it carries a source line, and the four intake desks (proposal, vendor pack, alarm faults, careers) are one click from every page.', screens: [
    ['home', 'Home page', ['Positions Sheriff for the energy sector with facts procurement teams check: ISO 9001:2015, ISO 45001:2018, Local Content certification and GAPSO 2024.', 'Three direct routes in: request a proposal, get the vendor pack, report an alarm fault.', 'Photographs are illustrative Wikimedia Commons images and are labelled as such.']],
    ['services', 'Services', ['Six lines of business, each named in public reporting, with a scope page per service.']],
    ['service-oil', 'Service page', ['Scope, sources, how a contract starts and a quote card that pre-fills the request form.']],
    ['industries', 'Industries', ['Sectors served, with the relevant services, and no client names without permission.']],
    ['hse', 'HSE & compliance', ['The page a supplier-qualification or HSE team reads first, linking straight to the vendor pack.', 'Training academy, workforce welfare and the GAPSO awards, all sourced.']],
    ['group', 'Group companies', ['Sheriff Security, Sheriff Alarms, Port & Marine, Training Academy, Construction and Medical Centre.']],
    ['leadership', 'Leadership', ['Executives as last reported in the press; titles to be confirmed before launch.']],
    ['rfp', 'Request for proposal', ['Industry, site type, posts, coverage, armed or unarmed, start date, term and documents.', 'Reference number on screen and by email; business development is alerted at once.']],
    ['vendor', 'Vendor prequalification pack', ['Procurement teams choose the documents they need; nothing is sent until a manager approves.']],
    ['alarm', 'Alarm service & fault report', ['Sheriff Alarms customers report faults and get a ticket number; urgent faults are flagged.']],
    ['careers', 'Careers', ['Open positions straight from the back office, with the documents to have ready.']],
    ['job', 'Job posting', ['Duties, requirements, location and closing date.']],
    ['apply', 'Online application', ['Police clearance, ID, certificates and CV uploaded privately; HR only.']],
    ['contact', 'Contact', ['Dedicated forms first, then editable office details and a message form.']],
  ] },
  { id: 'portal', eyebrow: 'Part 2', title: 'Private client and customer pages', intro: 'Pages reached through a private link or a lookup rather than the menu: the signed vendor-pack download page, what an expired link shows, and the alarm-ticket status page.', screens: [
    ['vendor-pack', 'Signed vendor pack', ['Opened from the approval email. The link is signed, expires on the date shown and can be withdrawn at any time.', 'Every download is written to the log the business-development team sees.']],
    ['vendor-expired', 'Expired or withdrawn link', ['Edited, expired or withdrawn links all end here, with a way to request a fresh pack.']],
    ['alarm-status', 'Alarm ticket status', ['Needs both the ticket number and the email used, and is rate-limited, so tickets cannot be guessed.']],
  ] },
  { id: 'admin', eyebrow: 'Part 3', title: 'The staff back office', intro: 'A private dashboard with three roles: administrator (everything), business development (proposals, vendor packs, compliance documents, alarm tickets, messages) and human resources (jobs and applications). Companies, people and documents in these screenshots are fictional sample data.', screens: [
    ['admin-dashboard', 'Dashboard', ['Leads with overdue proposal requests for business development and stale applications for HR.']],
    ['admin-board', 'Proposal board (drag & drop)', ['From first contact to won or lost; the first response is time-stamped automatically.']],
    ['admin-proposals', 'Proposal list', ['Search, status and industry filters, overdue flag.']],
    ['admin-proposal', 'Proposal request', ['All the request details, documents and the owner, value and notes panel.']],
    ['admin-vendor', 'Vendor pack requests', ['Every request with its status, link expiry and number of downloads.']],
    ['admin-vendor-pending', 'Approve or decline', ['One click to approve and email the signed link; declines send nothing.']],
    ['admin-vendor-approved', 'Live link and download log', ['Withdraw the link at any time; see exactly who downloaded what, and when.']],
    ['admin-compliance', 'Compliance documents', ['Upload the current certificate once; every future pack uses it. Expiring documents are flagged.']],
    ['admin-tickets', 'Alarm tickets', ['Urgent faults first; filters by status.']],
    ['admin-ticket', 'Alarm ticket', ['Status, technician visit, customer note and internal notes.']],
    ['admin-pipeline', 'Recruitment pipeline', ['Drag applicants from New to Screening, Interview and Training.']],
    ['admin-applications', 'Applications', ['Filters, document check and CSV export.']],
    ['admin-applicant', 'Applicant', ['Private document downloads and HR notes.']],
    ['admin-jobs', 'Jobs', ['Create, publish and close postings.']],
    ['admin-messages', 'Messages', ['Contact-form messages with a handled flag.']],
    ['admin-users', 'Users & roles', ['Add staff and set their role.']],
    ['admin-settings', 'Settings', ['Contact details, response target, link lifetime and alert recipients.']],
  ] },
];

const SCREEN_COUNT = SECTIONS.reduce((n, s) => n + s.screens.length, 0);

const FEATURES = [
  ['Public website', ['Home, 6 service pages, industries, HSE & compliance', 'Group companies, leadership, careers, contact', 'A cited source for every public claim', 'Phone-first layouts, light and dark themes', 'Sitemap, structured data, privacy and photo-credit pages']],
  ['Procurement desk', ['Request for proposal with private document uploads', 'Reference numbers and confirmation emails', 'Vendor prequalification pack with manager approval', 'Signed, expiring, revocable download links', 'Download log: time, document, IP, browser']],
  ['Customers & applicants', ['Alarm fault reports with ticket numbers', 'Ticket status by number + email', 'Online job applications', 'Police clearance, ID, certificates and CV kept private', 'Careers page fed by the back office']],
  ['Back office', ['Dashboard led by overdue proposals', 'Proposal board and recruitment pipeline (drag & drop)', 'Compliance library with expiry warnings', 'Alarm tickets, messages, jobs, CSV export', 'Roles: administrator, business development, HR']],
  ['Platform', ['Laravel 13 on PHP 8.3, MySQL', 'Runs on Hostinger shared hosting', 'HTTPS, rate limits, security headers', `${TESTS} automated tests, static analysis`, 'Editable settings: no developer needed']],
];

const sub = (c) => (c?.chunks ?? []);
function caption(key, i, c) {
  const text = CAPTIONS[key]?.[i];
  const n = c.chunks.length > 1 ? `${i + 1} of ${c.chunks.length} · ` : '';
  return text ? `<figcaption class="cap"><b>${esc(n)}</b>${esc(text)}</figcaption>` : '';
}

/**
 * Each screenshot slice becomes a flowable item; pack() (run in the page before printing) lays the
 * items onto letter pages by measured height, so a short slice shares a page instead of sitting alone.
 */
function screenItems(sec, [key, title, bullets]) {
  const c = chunks[key];
  if (!c) return `<div class="item" data-title="${esc(title)}" data-url="" data-first="1"><p class="muted">Screenshot missing: ${esc(key)}</p></div>`;
  return c.chunks.map((file, i) => `
    <div class="item" data-title="${esc(title)}" data-url="${esc(c.url)}" data-first="${i === 0 ? 1 : 0}">
      ${i === 0 ? `<div class="mini"><h3>${esc(title)}</h3><p class="url">${esc(c.url)}</p></div><ul class="bullets">${bullets.map((b) => `<li>${esc(b)}</li>`).join('')}</ul>` : ''}
      <figure class="${i === 0 ? `b${Math.min(bullets.length, 3)}` : ''}"><img src="${img(file)}" alt="${esc(title)}">${caption(key, i, c)}</figure>
    </div>`).join('');
}

/* Runs inside the page. Pages are 7.4in × 10in content boxes (Letter minus the @page margins). */
function pack() {
  const MIN_SCALE = 0.85, SHORT = 0.3, EMPTY = 0.4;
  for (const src of document.querySelectorAll('.flow-src')) {
    const tpl = src.querySelector('template.page-tpl').content;
    let body = null, scale = 1;
    const fits = () => used() <= body.clientHeight - 3;
    const used = () => { const last = body.lastElementChild; return last ? last.getBoundingClientRect().bottom - body.getBoundingClientRect().top : 0; };
    const setScale = (s) => { for (const i of body.querySelectorAll('img')) i.style.width = `${i.dataset.w0 * s}px`; };
    const place = (it) => { body.append(it); for (const i of it.querySelectorAll('img')) { i.style.width = ''; i.dataset.w0 = i.getBoundingClientRect().width; i.style.width = `${i.dataset.w0 * scale}px`; } };
    const newPage = (it) => {
      const pg = tpl.cloneNode(true).firstElementChild;
      const first = it.dataset.first === '1';
      pg.querySelector('.cont').hidden = first;
      pg.querySelector('h2').textContent = it.dataset.title;
      pg.querySelector('.shot-head .url').textContent = it.dataset.url;
      src.before(pg);
      body = pg.querySelector('.pbody'); scale = 1;
      it.querySelector('.mini')?.remove();
      place(it);
    };
    for (const it of [...src.querySelectorAll('.item')]) {
      if (!body) { newPage(it); continue; }
      const before = used();
      place(it);
      if (fits()) continue;
      const room = body.clientHeight;
      const itemH = it.getBoundingClientRect().height;
      let ok = false;
      if (itemH < room * SHORT || before < room * (1 - EMPTY)) {
        for (let s = Math.round(scale * 100) - 1; s >= MIN_SCALE * 100; s--) { setScale(s / 100); if (fits()) { scale = s / 100; ok = true; break; } }
      }
      if (ok) continue;
      it.remove(); setScale(scale);
      newPage(it);
    }
    src.remove();
  }
}
const foot = () => `<footer class="foot"><span class="fl">${LOGO(18, 'on-light')}Sheriff Security Services · Website proposal</span><span>Code Canvas Consultants Ltd · ${esc(DATE)}</span></footer>`;
const phone = (k, label) => `<figure><img src="${img(sub(chunks[k])[0])}" alt="${esc(label)}"><figcaption>${esc(label)}</figcaption></figure>`;
const tile = (k, i, label) => `<figure class="tile"><img src="${img(sub(chunks[k])[i])}" alt="${esc(label)}"><figcaption>${esc(label)}</figcaption></figure>`;

const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Sheriff Security Services – Website Proposal</title>
<style>
${FONTS}
  @page { size: Letter; margin: 0.5in 0.55in 0.5in; }
  * { box-sizing: border-box; }
  :root { --bg: #eef0f4; --surface: #fff; --ink: #14152e; --ink-2: #3e4160; --ink-3: #5a5d78; --line: #d5d9e3; --brand-950: #0d0e2b; --brand-900: #171848; --brand-800: #22246a; --blue: #3d5bd9; --link: #2e47c0; --sky: #aab8ff; --blue-soft: #e3e8fd; }
  html, body { margin: 0; padding: 0; font-family: "Red Hat Text", "Helvetica Neue", Arial, sans-serif; color: var(--ink); font-size: 10.2pt; line-height: 1.45; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  h1, h2, h3 { font-family: "Chakra Petch", "Arial Narrow", Arial, sans-serif; font-weight: 700; color: var(--brand-950); margin: 0; line-height: 1.05; letter-spacing: -0.005em; }
  a { color: var(--link); }
  .page { page-break-after: always; break-after: page; height: 10in; display: flex; flex-direction: column; overflow: hidden; }
  .page:last-child { page-break-after: auto; break-after: auto; }
  .muted { color: var(--ink-3); }
  /* permit-to-work eyebrow, as on the site */
  .ptw { font-family: "Red Hat Mono", Menlo, monospace; font-size: 8pt; letter-spacing: .14em; text-transform: uppercase; color: var(--ink-3); margin: 0 0 6px; display: flex; align-items: center; gap: 8px; }
  .ptw b { font-weight: 500; color: var(--link); background: var(--blue-soft); padding: 1px 6px; border-radius: 2px; letter-spacing: .08em; }
  .dark .ptw { color: rgba(255,255,255,.7); } .dark .ptw b { color: var(--sky); background: rgba(170,184,255,.14); }
  .graticule { background-color: var(--brand-950); background-image: linear-gradient(rgba(170,184,255,.07) 1px, transparent 1px), linear-gradient(90deg, rgba(170,184,255,.07) 1px, transparent 1px); background-size: 36px 36px; }
  img.logo { display: block; }
  /* cover */
  .cover { color: #fff; border-radius: 4px; padding: 0.6in 0.6in 0.5in; justify-content: space-between; position: relative; }
  .cover h1 { color: #fff; font-size: 44pt; line-height: 1; margin: 14px 0 16px; max-width: 6.4in; }
  .cover h1 span { color: var(--sky); }
  .cover p.lead { color: rgba(255,255,255,.85); font-size: 12.5pt; max-width: 5.8in; margin: 0; }
  .cover .coord { font-family: "Red Hat Mono"; font-size: 7.5pt; letter-spacing: .14em; color: rgba(170,184,255,.7); text-transform: uppercase; }
  .cover .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; border-top: 1px solid rgba(170,184,255,.25); padding-top: 16px; font-size: 9.2pt; color: rgba(255,255,255,.8); }
  .cover .meta b { display: block; font-family: "Red Hat Mono"; font-weight: 500; font-size: 7.5pt; letter-spacing: .16em; text-transform: uppercase; color: var(--sky); margin-bottom: 5px; }
  .cover .meta strong { color: #fff; }
  /* text pages */
  .prose h1 { font-size: 26pt; margin-bottom: 10px; }
  .prose h2 { font-size: 14pt; margin: 14px 0 6px; }
  .prose p { margin: 0 0 8px; }
  .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .card { border: 1px solid var(--line); border-radius: 4px; padding: 11px 13px; background: #fff; break-inside: avoid; }
  .card h3 { font-size: 11.5pt; margin-bottom: 5px; }
  .card ul { margin: 0; padding-left: 15px; } .card li { margin: 2px 0; }
  .tag { position: relative; border-top: 3px solid var(--blue); }
  .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 9px; margin: 8px 0 6px; } .stat { border-radius: 4px; padding: 11px 12px; color: #fff; } .stat b { display: block; font-family: "Chakra Petch"; font-size: 24pt; line-height: 1; color: var(--sky); } .stat span { font-size: 8.5pt; color: rgba(255,255,255,.82); }
  .note { border-left: 3px solid var(--blue); background: var(--blue-soft); padding: 8px 11px; font-size: 9pt; border-radius: 0 4px 4px 0; margin: 10px 0; }
  .src { font-size: 8pt; color: var(--ink-3); }
  table.feat { width: 100%; border-collapse: collapse; font-size: 9.3pt; } table.feat th, table.feat td { border-bottom: 1px solid var(--line); padding: 7px 8px; text-align: left; vertical-align: top; } table.feat th { font-family: "Red Hat Mono"; font-weight: 500; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .12em; color: var(--ink-3); background: var(--bg); } table.feat td:first-child { font-family: "Chakra Petch"; font-weight: 700; font-size: 11pt; color: var(--brand-950); width: 22%; }
  .pill-row { display: flex; flex-wrap: wrap; gap: 5px; } .pill { border: 1px solid #c4cdf7; background: #f3f5ff; color: var(--brand-800); border-radius: 3px; padding: 2px 8px; font-size: 8.4pt; font-weight: 500; }
  /* dividers */
  .section-divider { color: #fff; border-radius: 4px; padding: 0.75in 0.65in; justify-content: center; }
  .section-divider h1 { color: #fff; font-size: 40pt; margin: 10px 0 16px; } .section-divider p { color: rgba(255,255,255,.85); font-size: 12pt; max-width: 5.8in; }
  .section-divider ol { color: rgba(255,255,255,.85); columns: 2; font-size: 10pt; margin-top: 20px; padding-left: 20px; } .section-divider li { break-inside: avoid; margin: 3px 0; } .section-divider li::marker { color: var(--sky); font-family: "Red Hat Mono"; }
  /* screenshot pages */
  .shot-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; border-bottom: 2px solid var(--brand-950); padding-bottom: 6px; margin-bottom: 8px; }
  .shot-head h2 { font-size: 18pt; } .url { font-family: "Red Hat Mono"; font-size: 8pt; color: var(--ink-3); margin: 0; white-space: nowrap; }
  .bullets { margin: 0 0 8px; padding-left: 16px; font-size: 9.3pt; } .bullets li { margin: 1px 0; } .bullets li::marker { color: var(--blue); }
  figure { margin: 0; display: block; }
  .shot figure img { display: block; max-width: 100%; max-height: 7.75in; margin: 0 auto; border: 1px solid var(--line); border-radius: 3px; }
  .shot figure.b1 img { max-height: 7.45in; } .shot figure.b2 img { max-height: 7.2in; } .shot figure.b3 img { max-height: 6.95in; }
  .pbody { flex: 1 1 auto; min-height: 0; overflow: hidden; display: flex; flex-direction: column; gap: 16px; }
  .item { flex: none; } .item .mini { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; border-top: 1px solid var(--line); padding-top: 8px; margin-bottom: 5px; } .item .mini h3 { font-size: 13pt; }
  .flow-src { display: none; }
  .cap { margin: 7px 0 0; font-size: 8.8pt; line-height: 1.38; color: var(--ink-2); border-left: 3px solid var(--blue); padding-left: 9px; }
  .cap b { font-family: "Red Hat Mono"; font-weight: 500; color: var(--link); font-size: 8pt; }
  .phones { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin: 14px 0.15in 0; } .phones img { display: block; width: 100%; height: auto; border-radius: 22px; border: 7px solid var(--brand-950); } .phones figcaption { text-align: center; font-size: 9pt; color: var(--ink-2); margin-top: 6px; font-weight: 500; }
  /* vendor spread */
  .flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 10px 0 12px; counter-reset: s; }
  .flow div { border: 1px solid var(--line); border-top: 3px solid var(--blue); border-radius: 3px; padding: 9px 10px; background: #fff; font-size: 8.8pt; }
  .flow div::before { counter-increment: s; content: "0" counter(s); display: block; font-family: "Red Hat Mono"; font-size: 8pt; color: var(--link); letter-spacing: .1em; }
  .flow b { display: block; font-family: "Chakra Petch"; font-size: 11pt; color: var(--brand-950); margin: 2px 0 3px; }
  .tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; flex: 1; } .tile img { display: block; width: 100%; height: 3.55in; object-fit: cover; object-position: top left; border: 1px solid var(--line); border-radius: 3px; } .tile figcaption { font-size: 8.6pt; color: var(--ink-2); margin-top: 4px; line-height: 1.35; }
  /* pricing */
  .price { display: grid; grid-template-columns: 1.3fr 1fr; gap: 12px; align-items: stretch; margin: 8px 0 10px; }
  .price .big { color: #fff; border-radius: 4px; padding: 14px 16px; } .price .big b { display: block; font-family: "Chakra Petch"; font-size: 30pt; color: #fff; line-height: 1; } .price .big span { color: rgba(255,255,255,.82); font-size: 9.3pt; } .price .big .zero { margin-top: 10px; font-family: "Chakra Petch"; font-size: 15pt; color: var(--sky); }
  .yr { border: 2px solid var(--brand-950); border-radius: 4px; padding: 12px 14px; } .yr div { display: flex; justify-content: space-between; border-bottom: 1px solid var(--line); padding: 5px 0; font-size: 9.5pt; } .yr div:last-child { border: 0; } .yr strong { font-family: "Chakra Petch"; font-size: 12pt; }
  ul.tight { margin: 0; padding-left: 16px; font-size: 9.2pt; } ul.tight li { margin: 1.5px 0; }
  table.sla { width: 100%; border-collapse: collapse; font-size: 8.9pt; margin-top: 4px; } table.sla td, table.sla th { border-bottom: 1px solid var(--line); padding: 4px 6px; text-align: left; } table.sla th { font-family: "Red Hat Mono"; font-weight: 500; font-size: 7.4pt; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-3); }
  .weeks { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; margin: 8px 0 12px; } .weeks div { border: 1px solid var(--line); border-radius: 3px; padding: 10px 11px; font-size: 9.2pt; background: #fff; } .weeks b { display: block; font-family: "Chakra Petch"; font-size: 12pt; color: var(--brand-950); margin-bottom: 3px; } .weeks i { font-style: normal; font-family: "Red Hat Mono"; font-size: 7.8pt; color: var(--link); letter-spacing: .1em; text-transform: uppercase; }
  .next li { margin: 5px 0; }
  .contact { color: #fff; border-radius: 4px; padding: 16px 18px; margin-top: 14px; } .contact b { font-family: "Chakra Petch"; font-size: 15pt; display: block; } .contact p { margin: 4px 0 0; color: rgba(255,255,255,.85); }
  footer.foot { margin-top: auto; padding-top: 8px; font-size: 7.6pt; color: #8a8ea6; display: flex; justify-content: space-between; align-items: center; font-family: "Red Hat Mono"; letter-spacing: .04em; } footer.foot .fl { display: flex; align-items: center; gap: 8px; }
  .cover footer.foot, .section-divider footer.foot { display: none; }
</style></head><body>

<section class="page cover graticule dark">
  <div style="display:flex;justify-content:space-between;align-items:flex-start">${LOGO(64)}<span class="coord">06°48′N · 058°09′W · Georgetown</span></div>
  <div>
    <p class="ptw"><b>RFP-01</b>Website proposal · ${esc(DATE)}</p>
    <h1>A website, procurement desk and back office <span>for Sheriff Security Services</span></h1>
    <p class="lead">Sheriff has no working website today. This proposal shows a complete one, already built and running as a demo, with a vendor prequalification pack, request-for-proposal intake, alarm fault tickets and online recruitment. It costs US$1,000 a month, with nothing to pay for design and development.</p>
  </div>
  <div class="meta">
    <div><b>Prepared for</b><strong>Sheriff Security Services</strong> (Sheriff Security Inc.)<br>Sheriff Street, Georgetown, Guyana<br>Attention: Radica Ramdeen, Chief Executive Officer, and Ameir Ahmad, Managing Director (titles as reported in 2023)</div>
    <div><b>Prepared by</b><strong>Keron Lewis</strong>, Code Canvas Consultants Ltd<br>Port of Spain · codecanvastt.com<br>+1 868 466-CODE · keronlewis@live.com</div>
  </div>
</section>

<section class="page prose">
  <p class="ptw"><b>01</b>Executive summary</p>
  <h1>Why rebuild now</h1>
  <p><b>Sheriff has no working website.</b> When we checked on 23 October 2026, sssgy.net, the address listed for the company, served the hosting provider’s default cPanel page over plain HTTP, and its HTTPS address returned a Cloudflare 526 error (invalid SSL certificate at the origin). sheriffguyana.com redirects to a GoDaddy domain-parking page. An operator’s procurement officer who searches for Sheriff before a tender finds press coverage, but no company site, no documents and no way to request a quote.</p>
  <p><b>Worth doing either way:</b> WHOIS shows that sssgy.net, the domain listed for the company, expires on 10 November 2026. Renewing it in Sheriff’s name keeps the address with the company.</p>
  <p>The press coverage itself is strong. OilNOW reported in February 2023 that Sheriff runs ISO 9001:2015 and ISO 45001:2018 management systems and is certified by Guyana’s Local Content Secretariat. In December 2024 Guyana Standard reported that Sheriff won 13 of the 26 accolades at the first GAPSO awards. None of this is on a page Sheriff controls.</p>
  <div class="stat-grid">
    <div class="stat graticule"><b>${SCREEN_COUNT}</b><span>desktop screens built and shown in this proposal, plus 6 phone layouts</span></div>
    <div class="stat graticule"><b>${esc(TESTS)}</b><span>automated tests run on every change</span></div>
    <div class="stat graticule"><b>4</b><span>intake desks: proposals, vendor pack, alarm faults, careers</span></div>
    <div class="stat graticule"><b>3</b><span>staff roles: administrator, business development, HR</span></div>
  </div>
  <h2>What Sheriff gains</h2>
  <div class="cols">
    <div class="card tag"><h3>For operators &amp; procurement</h3><ul><li>Certifications, awards and services on one cited, professional site</li><li>The vendor pack: approved, time-limited, logged document sharing instead of email attachments</li><li>A proper request-for-proposal form with a 24-hour response target</li></ul></div>
    <div class="card tag"><h3>For business development</h3><ul><li>A dashboard that leads with unanswered requests</li><li>A drag-and-drop proposal board from New to Won</li><li>A compliance library with expiry warnings</li></ul></div>
    <div class="card tag"><h3>For Sheriff Alarms customers</h3><ul><li>Fault reports with ticket numbers, and urgent faults flagged</li><li>Status checks by ticket number and email, any time</li><li>A public note from staff; internal notes stay internal</li></ul></div>
    <div class="card tag"><h3>For recruitment</h3><ul><li>Online applications with police clearance, ID and certificates</li><li>Documents stored privately, for HR only</li><li>Recruitment pipeline, job postings and CSV export</li></ul></div>
  </div>
  <div class="note"><b>Sourced facts and illustrative photos.</b> The ISO, Local Content and award facts come from press coverage (OilNOW, Feb 2023; Guyana Standard, Dec 2024), not from Sheriff’s own records. Each is cited on the site and must be confirmed with Sheriff before launch. All photographs are illustrative Wikimedia Commons images of Guyana and the sector, not Sheriff’s own; each is credited on the photo-credits page.</div>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>02</b>At a glance</p>
  <h1>What is included</h1>
  <table class="feat"><thead><tr><th>Area</th><th>Features</th></tr></thead><tbody>
    ${FEATURES.map(([a, f]) => `<tr><td>${esc(a)}</td><td><div class="pill-row">${f.map((x) => `<span class="pill">${esc(x)}</span>`).join('')}</div></td></tr>`).join('')}
  </tbody></table>
  <h2>How the pieces connect</h2>
  <p>An operator’s supplier team finds Sheriff, reads the <b>HSE &amp; compliance</b> page and requests the <b>vendor pack</b>. A manager approves the request in the <b>back office</b>, and the requester receives a signed link that works for 7 days (editable), can be withdrawn and logs every download. The same team can send a <b>request for proposal</b> with tender documents. It lands on the proposal board, and the dashboard flags it if nobody has responded within 24 hours. Sheriff Alarms customers log faults and check progress with a ticket number. Recruits apply online, and HR moves them through the pipeline.</p>
  <h2>Content and imagery</h2>
  <p>Every public statement comes from a cited source: OilNOW, Guyana Standard, Stabroek News, Kaieteur News, the Sheriff Guyana LinkedIn profile and Sheriff’s own 2021 website (Wayback Machine). The address and phone are the ones on Sheriff’s own contact page (archived February 2023), and they match the Georgetown Chamber of Commerce directory. They are editable settings and will be re-confirmed at kick-off. The logo is Sheriff’s own, from its website. The working demo can be shown live on a call.</p>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>03</b>Headline feature</p>
  <h1>The vendor prequalification pack</h1>
  <p>Operators and their contractors prequalify every supplier, and each one asks for the same documents: ISO certificates, insurance, the HSE and quality policies, Local Content, GRA and NIS certificates. Today those go out as email attachments that nobody can withdraw or track. The vendor pack makes it a controlled, auditable process that takes a manager one click.</p>
  <div class="flow">
    <div><b>Request</b>The procurement officer ticks the documents needed and gives the tender or registration reference. They get a reference number by email.</div>
    <div><b>Approve</b>A Sheriff manager checks who is asking. Nothing is sent automatically, and a decline sends nothing at all.</div>
    <div><b>Private link</b>Approval emails a signed link that expires after 7 days (editable). Any edit to the link breaks it, and it can be withdrawn instantly.</div>
    <div><b>Logged</b>Every download records the time, document, IP address and browser, so Sheriff knows exactly where its documents went.</div>
  </div>
  <div class="cols">
    <div class="card"><h3>How it is protected</h3><ul><li>Files sit in private storage outside the website folder and are never publicly addressable</li><li>Links are cryptographically signed with an expiry, and a withdrawn or expired link shows a plain “request again” page</li><li>A double-clicked Approve cannot re-issue the link or send a second email</li><li>Downloads are rate-limited; only business development and administrators can approve</li></ul></div>
    <div class="card"><h3>How it is kept current</h3><ul><li>Upload each certificate once in the compliance library; every future pack uses the current file</li><li>Expiry dates are tracked, and the dashboard flags documents about to lapse</li><li>Choose which documents procurement teams can request</li><li>The approval email, link lifetime and alert recipients are all editable</li></ul></div>
  </div>
  ${foot()}
</section>

<section class="page">
  <header class="shot-head"><div><p class="ptw"><b>03</b>Headline feature · continued</p><h2>The vendor pack, end to end</h2></div><p class="url">sample data</p></header>
  <div class="tiles">
    ${tile('vendor', 0, '1 · Public request: the procurement team chooses documents from the compliance library.')}
    ${tile('admin-vendor-pending', 0, '2 · Back office: a manager approves (emails the signed link) or declines (sends nothing).')}
    ${tile('vendor-pack', 1, '3 · The private pack: expiry date and time, one download button per approved document.')}
    ${tile('admin-vendor-approved', 0, '4 · Live link, one-click withdraw and the download log with time, document, IP and browser.')}
  </div>
  ${foot()}
</section>

${SECTIONS.map((sec) => `
<section class="page section-divider graticule dark">
  <p class="ptw"><b>${esc(sec.eyebrow)}</b>Screens</p>
  <h1>${esc(sec.title)}</h1>
  <p>${esc(sec.intro)}</p>
  <ol>${sec.screens.map((s) => `<li>${esc(s[1])}</li>`).join('')}</ol>
</section>
<div class="flow-src">
  <template class="page-tpl"><section class="page shot">
    <header class="shot-head"><div><p class="ptw"><b>${esc(sec.eyebrow)}</b>${esc(sec.title)}<span class="cont"> · continued</span></p><h2></h2></div><p class="url"></p></header>
    <div class="pbody"></div>
    ${foot()}
  </section></template>
  ${sec.screens.map((s) => screenItems(sec, s)).join('')}
</div>
`).join('')}

<section class="page">
  <header class="shot-head"><div><p class="ptw"><b>04</b>Responsive design</p><h2>Built for phones first</h2></div><p class="url">390 × 844</p></header>
  <p style="margin:0 0 4px">Site supervisors, procurement officers and recruits often arrive on a phone. Every public page adapts to small screens, and a fixed bar keeps Call, Alarm fault and Get a proposal within thumb reach.</p>
  <div class="phones">${phone('m-home', 'Home')}${phone('m-vendor', 'Vendor pack request')}${phone('m-rfp', 'Request for proposal')}</div>
  ${foot()}
</section>
<section class="page">
  <header class="shot-head"><div><p class="ptw"><b>04</b>Responsive design · continued</p><h2>The private pack and the back office on a phone</h2></div><p class="url">390 × 844</p></header>
  <p style="margin:0 0 4px">Procurement officers can open the pack from their inbox, and managers can approve requests and work the overdue list from the field. Sample data.</p>
  <div class="phones">${phone('m-vendor-pack', 'Signed vendor pack')}${phone('m-admin-dashboard', 'Dashboard (staff)')}${phone('m-admin-vendor', 'Approve a request (staff)')}</div>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>05</b>Technology, hosting &amp; security</p>
  <h1>Built to run simply and safely</h1>
  <div class="cols">
    <div class="card"><h3>Platform</h3><ul><li>Laravel 13 on PHP 8.3 with MySQL: open-source, widely supported, no licence fees</li><li>Tailwind CSS and Alpine.js; fonts served from the site itself, not a font CDN</li><li>Runs on Hostinger shared hosting: one zip and a guided installer, with no background workers or extra servers</li><li>${esc(TESTS)} automated tests and PHPStan static analysis (level 6) run before every release</li></ul></div>
    <div class="card"><h3>Security &amp; privacy</h3><ul><li>HTTPS enforced, with HSTS and security headers on every response</li><li>Applicant, proposal and compliance files kept in private storage, streamed only to signed-in staff with the right role or through a signed vendor link</li><li>Rate limits on every form, sign-in, ticket lookup and download; spam honeypots</li><li>CSV exports guarded against spreadsheet-formula injection</li></ul></div>
    <div class="card"><h3>Roles &amp; control</h3><ul><li>Administrator: everything, including users and settings</li><li>Business development: proposals, vendor packs, compliance documents, alarm tickets, messages</li><li>Human resources: jobs and applications</li><li>Contact details, response target, link lifetime and alert recipients editable in Settings</li></ul></div>
    <div class="card"><h3>Reach &amp; quality</h3><ul><li>XML sitemap, structured data and descriptive page titles for search engines</li><li>Responsive on phones, tablets and desktops, with light and dark themes</li><li>Text colours checked for WCAG AA contrast; keyboard and screen-reader labels</li><li>Responsive WebP photography at three sizes</li></ul></div>
  </div>
  <h2>Emails included</h2>
  <p>Proposal request received (to the client) and the office alert; vendor pack request received, and the approval email with the signed link; alarm ticket confirmation with the status link; new application alert; contact-form alert. All are sent from a Sheriff mailbox.</p>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>06</b>Investment</p>
  <h1>The US$1,000 per month plan</h1>
  <div class="price">
    <div class="big graticule"><b>US$1,000 / month</b><span>Invoiced in US dollars. No setup fee, no deposit, no minimum term.</span><div class="zero">US$0 for design and development</div></div>
    <div class="yr"><div><span>Year one</span><strong>US$12,000</strong></div><div><span>Or prepaid annually (10% off)</span><strong>US$10,800</strong></div><div><span>Build cost</span><strong>US$0</strong></div><div><span>Handover fee, only if you leave</span><strong>US$7,000</strong></div></div>
  </div>
  <div class="cols">
    <div class="card"><h3>Terms</h3><ul class="tight"><li>Billing starts at the end of the month in which the site launches, prorated for the days it was live. Invoices are monthly and due within 14 days.</li><li>No minimum term. Either side can end the plan with 30 days’ notice by email.</li><li>The price is fixed for the first 24 months. Optional annual prepayment at 10% off: US$10,800 a year.</li><li>Handover fee of US$7,000, only if you leave. It buys the site outright and moves it to hosting in Sheriff’s name. It is waived if Code Canvas stops trading.</li><li>Your content, domain and data belong to Sheriff at all times.</li></ul></div>
    <div class="card"><h3>Included in the plan</h3><ul class="tight"><li>Complete design and build of everything in this proposal</li><li>Managed hosting with CDN and SSL</li><li>Daily backups plus a weekly off-site copy</li><li>Security patches and software updates</li><li>24/7 uptime monitoring</li><li>Up to 10 hours a month of content changes and small improvements</li><li>Staff training</li><li>Google Analytics, Search Console and a monthly summary</li><li>Email, WhatsApp and phone support, plus a quarterly review call</li><li>A 30-day post-launch warranty</li></ul></div>
  </div>
  <div class="cols" style="margin-top:10px">
    <div class="card"><h3>Response times</h3><table class="sla"><tr><th>Issue</th><th>Response</th><th>Fix target</th></tr><tr><td>Site down or forms not sending</td><td>1 hour</td><td>4 hours</td></tr><tr><td>Something on a page broken</td><td>4 business hours</td><td>1 business day</td></tr><tr><td>Content change</td><td>1 business day</td><td>Done in 2</td></tr></table></div>
    <div class="card"><h3>Quoted separately, only with your approval</h3><ul class="tight"><li>Work beyond 10 hours in a month: US$50 per hour, or a fixed quote</li><li>Online card payments (e.g. alarm-monitoring fees)</li><li>SMS or WhatsApp notifications for tickets and approvals</li><li>Integrations with alarm-monitoring, guard-tracking or HR systems</li><li>A client portal for guard reports or contract documents</li><li>Professional photography or video of Sheriff’s own operations</li><li>Paid advertising and SEO campaigns</li></ul></div>
  </div>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>07</b>Timeline</p>
  <h1>Six weeks from approval to launch</h1>
  <div class="weeks">
    <div><i>Week 1</i><b>Kick-off</b>Kick-off call. We collect the original logo files and content, and apply them.</div>
    <div><i>Week 2</i><b>Revision round 1</b>Your first round of changes. We configure forms, emails and roles with Sheriff’s real addresses and staff.</div>
    <div><i>Week 3</i><b>Data &amp; content</b>We load the real compliance documents, open positions, services copy and confirmed facts.</div>
    <div><i>Week 4</i><b>Revision round 2</b>Your second round of changes across the site and back office.</div>
    <div><i>Week 5</i><b>Formal testing</b>Your staff use every form, the vendor pack, tickets and both pipelines on phones and desktops.</div>
    <div><i>Week 6</i><b>Launch</b>We switch on the domain, redirect old addresses, submit to Search Console and train your staff.</div>
  </div>
  <h2>What we need from Sheriff</h2>
  <ul>
    <li>The original logo artwork, if you have it (the demo uses the logo from Sheriff’s website)</li>
    <li>Confirmation of the phone, email and address (the demo uses those on Sheriff’s own contact page)</li>
    <li>Confirmation of every published fact, including certifications, awards, group companies and leadership titles</li>
    <li>The current compliance documents for the vendor pack (certificates, insurance, policies, GRA and NIS) with their expiry dates</li>
    <li>Access to the sssgy.net and sheriffguyana.com domain settings, and the mailboxes for alerts and replies</li>
    <li>Staff names and roles for the back office, and the positions to advertise at launch</li>
    <li>Sheriff’s own photography, if preferred to the illustrative images</li>
  </ul>
  <div class="note"><b>Billing does not start until launch.</b> The build, both revision rounds, testing and the preview site cost nothing. The first invoice comes at the end of the month in which the site goes live, prorated.</div>
  ${foot()}
</section>

<section class="page prose">
  <p class="ptw"><b>08</b>Next steps</p>
  <h1>From proposal to launch</h1>
  <ol class="next">
    <li><b>Review this proposal and the working demo.</b> We can walk through it live on a call: the public site, the vendor pack from request to download, and each staff role.</li>
    <li><b>Send any changes</b> to pages, wording or workflow. The first revision round can start straight away.</li>
    <li><b>Sign a one-page agreement</b> on these terms: US$1,000 a month, month to month on 30 days’ notice, with a US$7,000 handover fee only if you leave.</li>
    <li><b>Kick-off call</b> the same week, then launch six weeks later.</li>
  </ol>
  <div class="contact graticule">
    <b>Keron Lewis</b>
    <p>Director, Code Canvas Consultants Ltd · Port of Spain, Trinidad and Tobago</p>
    <p>keronlewis@live.com · +1 868 466-CODE · codecanvastt.com</p>
  </div>
  <p class="src" style="margin-top:14px">Sources for client facts: OilNOW, “Sheriff Security: the prototype for oil and gas security in Guyana”, Feb 2023 (promoted feature); Guyana Standard, “Sheriff Security cops 13 accolades at GAPSO awards ceremony”, 20 Dec 2024; Stabroek News, 31 Dec 2023; sssgy.net (2021–2024, Wayback Machine); Georgetown Chamber of Commerce member directory. Leadership titles are as reported in 2023 and should be confirmed. Website status checked 23 October 2026; domain expiry from the public WHOIS record, 24 October 2026.</p>
  ${foot()}
</section>
</body></html>`;

fs.writeFileSync(OUT_HTML, html);
const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--allow-file-access-from-files'] });
const page = await browser.newPage();
await page.goto('file://' + OUT_HTML, { waitUntil: 'networkidle0' });
await page.evaluate(() => document.fonts.ready);
// Lay out on screen at the printed content width (7.4in) so measured heights match the PDF.
await page.setViewport({ width: 710, height: 960 });
await page.emulateMediaType('print');
await page.evaluate(() => Promise.all([...document.images].map((i) => i.decode().catch(() => {}))));
await page.evaluate(`(${pack.toString()})()`);
fs.writeFileSync(OUT_HTML.replace(/\.html$/, '.packed.html'), await page.content());
await page.pdf({ path: OUT_PDF, format: 'Letter', printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false });
await browser.close();
console.log('PDF written:', OUT_PDF, Math.round(fs.statSync(OUT_PDF).size / 1024 / 1024 * 10) / 10 + ' MB');
