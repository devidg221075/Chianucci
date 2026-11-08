// Verifica nel browser le regole della tavola grafica: spaziature a 8 punti, contenitore/griglia, scala dei titoli.
// Uso: BASE=http://127.0.0.1:8123 NODE_PATH=$(npm root -g) node scripts/audit-grafica.cjs [percorso ...]
const { chromium } = require('playwright');
const BASE = process.env.BASE || 'http://127.0.0.1:8123';
const PATHS = process.argv.slice(2).length ? process.argv.slice(2) : ['/it/', '/it/azienda', '/it/catalogo', '/it/contatti', '/en/'];
const HEADINGS = { // px / riga per ampiezza
  mobile: { h1: [36, 48], h2: [28, 40], h3: [20, 32] },
  tablet: { h1: [44, 56], h2: [32, 40], h3: [20, 32] },
  desktop: { h1: [56, 64], h2: [32, 40], h3: [20, 32] },
};
(async () => {
  const browser = await chromium.launch();
  let total = 0;
  for (const [name, width] of [['mobile', 390], ['tablet', 900], ['desktop', 1280]]) {
    const page = await (await browser.newContext({ viewport: { width, height: 900 } })).newPage();
    for (const path of PATHS) {
      await page.goto(BASE + path, { waitUntil: 'networkidle' });
      const report = await page.evaluate((scale) => {
        const bad = [];
        const px = (v) => parseFloat(v) || 0;
        const label = (el) => el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/).slice(0, 3).join('.') : '');
        const visible = (el) => { const r = el.getBoundingClientRect(); const cs = getComputedStyle(el); return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && cs.display !== 'none'; };
        for (const el of document.querySelectorAll('header *, main *, footer *, header, main, footer')) {
          if (!visible(el) || el.closest('svg')) continue;
          const cs = getComputedStyle(el);
          const inline = cs.display === 'inline';
          for (const prop of ['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'rowGap', 'columnGap']) {
            if (inline && prop.startsWith('padding')) continue;
            const v = cs[prop] === 'normal' ? 0 : px(cs[prop]);
            if (v % 8 !== 0) bad.push(`${prop}=${v}px ${label(el)}`);
          }
          if (!inline) for (const prop of ['marginTop', 'marginBottom', 'marginLeft', 'marginRight']) {
            const raw = cs[prop]; if (raw === 'auto') continue;
            const v = px(raw); if (v % 8 !== 0 && !(prop.includes('Left') || prop.includes('Right')) ) bad.push(`${prop}=${v}px ${label(el)}`);
          }
        }
        for (const [tag, [size, lh]] of Object.entries(scale)) {
          for (const el of document.querySelectorAll('main ' + tag + ', footer ' + tag)) {
            if (!visible(el)) continue;
            const cs = getComputedStyle(el);
            if (px(cs.fontSize) !== size || px(cs.lineHeight) !== lh) bad.push(`${tag} ${px(cs.fontSize)}/${px(cs.lineHeight)} (atteso ${size}/${lh}): ${el.textContent.trim().slice(0, 30)}`);
          }
        }
        const fonts = new Set([...document.querySelectorAll('main *, header *, footer *')].filter(visible).map((e) => getComputedStyle(e).fontFamily.split(',')[0].replace(/["']/g, '')));
        for (const f of fonts) if (!/Montserrat|Inter/.test(f) && f !== 'system-ui') bad.push('font non previsto: ' + f);
        const c = document.querySelector('.contenitore');
        const inner = c ? c.getBoundingClientRect().width - px(getComputedStyle(c).paddingLeft) * 2 : null;
        return { bad: [...new Set(bad)], inner };
      }, HEADINGS[name]);
      const maxInner = width >= 1248 ? 1200 : width - (width >= 768 ? 48 : 32);
      const ok = report.inner === null || Math.round(report.inner) === maxInner;
      console.log(`${name.padEnd(8)} ${path.padEnd(14)} violazioni: ${report.bad.length}${ok ? '' : ' | contenitore ' + report.inner + ' invece di ' + maxInner}`);
      for (const b of report.bad.slice(0, 8)) console.log('    -', b);
      total += report.bad.length + (ok ? 0 : 1);
    }
  }
  console.log(total === 0 ? 'TUTTO CONFORME' : `TOTALE VIOLAZIONI: ${total}`);
  await browser.close();
  process.exit(total === 0 ? 0 : 1);
})();
