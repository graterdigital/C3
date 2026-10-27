// Full-page screenshots of the local rig at desktop + mobile widths.
//   NODE_PATH=$(npm root -g) node bin/shots.js [baseUrl] [path ...]
//   default: http://localhost:8090  /  /about/ /services/ /reviews/ /faq/ /contact/
const { chromium } = require('playwright');
const base = process.argv[2] && process.argv[2].startsWith('http') ? process.argv[2] : 'http://localhost:8090';
const paths = process.argv.slice(process.argv[2] && process.argv[2].startsWith('http') ? 3 : 2);
const pages = paths.length ? paths : ['/', '/about/', '/services/', '/reviews/', '/faq/', '/contact/'];
const exe = process.env.CHROMIUM || '/opt/pw-browsers/chromium';
const fs = require('fs'); fs.mkdirSync('preview/shots', { recursive: true });

(async () => {
  const b = await chromium.launch({ executablePath: fs.existsSync(exe) ? exe : undefined, args: ['--no-sandbox'] });
  for (const w of [1440, 390]) {
    for (const path of pages) {
      const name = (path === '/' ? 'home' : path.replace(/^\/|\/$/g, '').replace(/\//g, '-')) + (w < 500 ? '-mobile' : '');
      const p = await b.newPage({ viewport: { width: w, height: w < 500 ? 844 : 900 } });
      // stub map tiles (sandbox may block them); harmless elsewhere
      await p.route(/tile\.opentopomap\.org/, r => r.fulfill({ status: 200, contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256"><rect width="256" height="256" fill="#e8e3d6"/></svg>' }));
      await p.goto(base + path, { waitUntil: 'networkidle' });
      await p.evaluate(() => document.querySelectorAll('.gd-reveal').forEach(e => e.classList.add('is-visible')));
      await p.waitForTimeout(400);
      await p.screenshot({ path: `preview/shots/${name}.png`, fullPage: true });
      console.log(name, 'ok');
      await p.close();
    }
  }
  await b.close();
})();
