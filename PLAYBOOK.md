# WP Site Kit — Grater Digital

Starter theme + plugins + tooling for rebuilding a client site in WordPress fast. Proven on huayhuash-trek.com (Oct 2026).

```
wp-content/themes/gd-starter/     theme — edit config/site.php, theme.json, pattern-parts/; pick a direction in assets/css/directions/
DIRECTIONS.md                     the design-direction roster + log — read before any CSS; never ship the base look twice
wp-content/plugins/gd-reviews/    reviews CPT; sources: Apify (TripAdvisor full history), TripAdvisor Content API, Google Places, JSON feed, CSV
wp-content/plugins/gd-instagram/  Instagram grid; sources: Apify public-profile scrape (default) or official Instagram API
optional/gd-treks/                example domain CPT with repeater (itinerary), gallery, Leaflet map, accordion block — copy & adapt for "offerings"
scripts/seed.php                  builds pages/menus/logo from content/site.json; idempotent
scripts/configure-*.php           one-shot WP-CLI setup for the plugin sources
bin/dev-up.sh                     local WP (SQLite, no MySQL) in ~/wp-rig, theme/plugins symlinked
bin/shots.js                      Playwright full-page screenshots (desktop + mobile)
bin/snapshot.py                   static HTML snapshot of the local site (for an artifact preview)
content/site.json                 the content model the seed reads
```

## Process (what worked)

1. **Brief.** Old site URL(s), sample/reference sites, hosting target, DNS owner, what stays / what changes.
2. **Crawl → inventory.** Spin a subagent: fetch every page, capture verbatim copy, images (find the originals — strip resize segments), forms, reviews, FAQs → `content/inventory.json` + `INVENTORY.md` + `redirects.csv` + `images.txt`. Lorem-ipsum pages and misspellings get flagged. *WebFetch summarises; use the browser + DOMParser for verbatim text.*
3. **Content model.** Translate the inventory into `content/site.json` (pages → blocks/patterns) and, if the domain needs a CPT, copy `optional/gd-treks` and rename.
4. **Direction.** Read `DIRECTIONS.md`. Pick a direction from the roster that the last three builds didn't use, or write a new one. Write the five-line plan (palette, type, layout + hero archetype, the one bold move, what to delete) *before* touching CSS. Set `direction` + `fonts_url` in `config/site.php`, match `theme.json`, copy the hero archetype over `pattern-parts/hero.php`. Never ship the base tokens.
5. **Brand.** `config/site.php` (NAP, socials, CTA, WhatsApp on/off), `theme.json` palette/fonts, `assets/img/` photos (1920w JPEG q82). Patterns in `pattern-parts/` are placeholder copy — write real copy there or in `site.json`. Strip the tells listed in DIRECTIONS.md from every pattern you touch.
6. **Local rig.** `bin/dev-up.sh` → seed → `bin/shots.js` → look at the screenshots at 1440 and 390 before anyone else does. Check `wp-content/debug.log` is empty.
7. **Preview.** `bin/snapshot.py` → publish as an artifact for the client/stakeholders, or push to staging.
8. **Deploy.** Zip `wp-content content scripts`, SFTP to host, `unzip -o`, `wp theme activate`, `wp plugin activate`, `wp eval-file scripts/seed.php`, `rm -rf content scripts`, `wp cache flush`. **chmod 755/644 after unzip** — zips built as root land unreadable on Kinsta/WP Engine.
9. **Sources.** `APIFY_TOKEN=… wp eval-file scripts/configure-reviews.php` / `configure-instagram.php`. Apify free tier covers both.
10. **Pre-launch.** 301s from `redirects.csv` at the host, SMTP plugin for `wp_mail`, `blog_public` back to 1, official brand logos uploaded in Customizer for the rating cards, TripAdvisor display terms (logo + link) if their reviews are shown.

## Gotchas learned

- Don't name a theme folder `patterns/` unless files have pattern headers — WP auto-registers and warns. Use `pattern-parts/`.
- Nested `alignwide` inside `alignfull` sections has no width in a classic theme — the kit's `.gd-canvas .alignfull > :not(.alignfull)` rule handles it.
- `wp eval-file` rejects unknown `--flags`; pass options as env vars.
- SQLite plugin trunk is a monorepo; use the `v2.2.9` tag.
- Full-page Playwright screenshots show lazy images as blank — not a site bug; the `shots.js` eager-swap doesn't fix `sizes=auto`. Screenshot the viewport for anything below the fold.
- Instagram CDN URLs are signed; always sideload. Meta's official API needs a Business app + tester invite + client login — Apify public scrape avoids all of it.
- TripAdvisor Content API returns only 5 reviews; Apify `maxcopell/tripadvisor-reviews` gets the history for <$1.
- Theme images: reference via `gd_img('file.jpg')`; the seed imports them to the Media Library and a `the_content` filter rewrites theme URLs → attachments, so folder permissions can't blank a hero.

## Conventions

- Prefix `gd_` / `gd-` / `GD_`; text domain `gd-starter`; block namespace `gd/`.
- Plugins read theme tokens (`--gd-accent` etc.) with fallbacks, so they work on any theme.
- Every plugin: settings page, WP-CLI command, idempotent sync, "last run" status line.
- All copy that is client-specific lives in `config/site.php`, `content/site.json` or `pattern-parts/` — never in `inc/`.
- All *taste* lives in `assets/css/directions/<name>.css` — `main.css` holds layout mechanics and `--gd-*` defaults only. If you find yourself editing a radius, shadow, font or colour in `main.css`, it belongs in the direction file.
- Plugins (gd-events, gd-instagram, gd-reviews) read `--gd-*` tokens, so they re-skin with the direction automatically.
