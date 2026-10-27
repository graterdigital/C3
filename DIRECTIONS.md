# Design directions

The kit's base theme is deliberately plain. **Never ship the base tokens.** Every client site picks (or gets) a *direction*: a named visual identity with its own palette, type, layout rules and hero archetype. Directions live in `wp-content/themes/gd-starter/assets/css/directions/<name>.css` and are selected with `'direction' => '<name>'` in `config/site.php` (plus `'fonts_url'` for that direction's typefaces). The direction file loads after `main.css` and overrides the `--gd-*` tokens plus whichever component rules it needs to restructure.

Why this exists: with one starter and one set of defaults, every site comes out looking like the same site with different hex codes. The roster below is the antidote. Two sites in the same vertical, or two sites built in the same quarter, must not share a direction.

## The rule

1. **Read the brief for its material world** before picking: what the client's flyers, shop, product, uniforms, logo and photos actually look like. The direction is derived from that, not from the industry label.
2. **Check the log at the bottom.** Don't reuse the last three directions used. If the right one is on the list, make a new one or a named variant (`workshop-b`) that changes at least the hero archetype and the type family.
3. **Write the plan first** — five lines: palette (4–6 named hexes), type (one or two families, roles), layout concept (one sentence + which hero archetype), the one bold move, what gets removed. Then build.
4. **Delete the tells** regardless of direction: tracked-out ALL-CAPS eyebrows; `01 / 02 / 03` numerals on things that aren't sequences; identical rounded cards with the same soft shadow; `→` on links; middle-dot meta strings; fade-up-on-scroll on every section; one accent word in a headline; cream + serif + terracotta.
5. **Screenshot at 1440 and 390, look, then remove one thing.**

## Roster

Each direction defines: palette · type · layout · hero · rules · good for. Three are implemented as CSS; the rest are briefs to build from when a client calls for them.

### workshop — implemented (`directions/workshop.css`)
- **Palette:** flyer navy `#0f1e38`, pantry red `#d32f2a`, highlighter `#f5d33f`, primer `#f7f6f1`, plywood `#e8dcc4`, graphite `#1c1f24`.
- **Type:** IBM Plex Sans, one family. 700 for display, tight tracking; 400 body. (Started as Bricolage Grotesque — client found it too playful.)
- **Layout:** hard-edged tile grid, 3px gaps with the page showing through, 1.5px ink rules, zero radius, zero shadow.
- **Hero:** `hero-split` — flat colour block with the headline beside a photo tile, both butted to a ruled header.
- **Rules:** highlighter underlines on links; checkerboard of photo tiles and flat colour tiles for services; steps as ruled columns with plain numerals; mission as a navy block with a huge statement.
- **Good for:** trades, makers, mutual aid, DIY, anyone whose brand lives on flyers and shop walls. *Used: C3 (Oct 2026).*

### alpine — the original kit look, now a direction (`directions/alpine.css`)
- **Palette:** ink `#1b2430`, paper `#f7f4ee`, sand `#ede7da`, terracotta `#c8652b`, glacier `#2f6f8f`, pine `#2e5a3e`.
- **Type:** Fraunces display + Inter body.
- **Layout:** soft 14px radius cards, warm paper field, full-bleed photo covers with gradient overlays, sticky translucent header.
- **Hero:** `hero-cover` — full-viewport photo with gradient, eyebrow, headline, two pills, proof row.
- **Good for:** travel, outdoors, hospitality. *Used: Huayhuash (Oct 2026).* **Retire for at least the next three builds** — it is also the most "AI-looking" of the set.

### ledger — implemented (`directions/ledger.css`)
- **Palette:** bone `#f4f3ee`, carbon `#17191c`, steel `#5a6470`, one signal colour per client (default oxblood `#7a1f2b`), hairline `#c9c7bf`.
- **Type:** Instrument Serif for display (large, light, no bold), Instrument Sans for body and UI.
- **Layout:** two-column editorial grid, 1px hairlines, generous margins, left-aligned everything, no cards — content sits directly on the page separated by rules; images are full-column, never cropped to squares.
- **Hero:** `hero-statement` — no photo: a very large serif statement, a short paragraph, one button, a thin rule, and the first photo appears only in section two.
- **Rules:** sentence-case small labels in sans; numbers set in serif; buttons are text with a hairline border; no colour fills except the signal colour on exactly one element per page.
- **Good for:** professional services, law, finance, consultancies, B2B industrial.

### signal — brief only
- **Palette:** white `#ffffff`, black `#0a0a0a`, one saturated client colour (electric blue `#1b4dff`, or orange `#ff5a1f`), cool grey `#eef0f3`.
- **Type:** Space Grotesk or Archivo Expanded for display, system sans for body.
- **Layout:** big type, big whitespace, product or screenshot imagery with hard drop shadows (offset, no blur), 4px radius max.
- **Hero:** `hero-product` — headline left, product/screenshot right breaking out of the grid edge.
- **Good for:** SaaS, e-commerce with a hero product, tech services, Rugged Depot-style product catalogues.

### clinic — brief only
- **Palette:** mist `#f2f6f7`, deep teal `#0e3b45`, blush `#f3d9d2`, white, charcoal text.
- **Type:** DM Serif Display or Cormorant for display (light weight, large), Figtree body.
- **Layout:** rounded 24px radius on photos only (not cards), lots of air, photo-led, centred headlines allowed; soft tonal section backgrounds instead of rules.
- **Hero:** `hero-portrait` — portrait photo of a person taking 60% of the width, headline overlapping the edge of the photo on a soft tint block.
- **Good for:** medical spas, clinics, wellness, salons. Must not use terracotta.

### civic — brief only
- **Palette:** navy `#1d2d5a`, federal gold `#d8a93b`, off-white `#fbfaf7`, slate `#454c5c`, light stone `#e9e6df`.
- **Type:** Libre Franklin throughout (wide weight range), or Public Sans.
- **Layout:** strict 12-column, dense information blocks, tables welcome, 2px radius, flat colour bars as section headers.
- **Hero:** `hero-banner` — short band with headline and a search or quick-links row, not a photo.
- **Good for:** associations, municipalities, schools, real estate brokerages with listings.

### field — brief only
- **Palette:** sage `#9fae8f`, soil `#4b3b2f`, straw `#e8dcb5`, chalk `#f5f3ec`, tomato `#c6422b` as the one bright.
- **Type:** Fraunces at *heavy* weight with wide optical size (very different from alpine's light Fraunces), Karla body.
- **Layout:** asymmetric: 5/7 column splits that alternate sides, photos with a 1px soil border and a 6px offset solid shadow in straw, hand-set feel.
- **Hero:** `hero-offset` — photo with the offset shadow, headline overlapping its bottom-left corner.
- **Good for:** farms, food, landscaping, nurseries, markets, breweries.

## Hero archetypes shipped as patterns
`pattern-parts/hero-cover.php` (alpine), `pattern-parts/hero-split.php` (workshop). `hero.php` is whichever one the client uses — copy the archetype over it. The other archetypes above are described well enough to build in one pass; add the pattern file next to these when you do.

## Adding a direction
1. Copy `directions/workshop.css` as a starting skeleton (tokens block first, then components).
2. Set `direction` + `fonts_url` in `config/site.php`; update `theme.json` palette and `fontFamilies` to match so the editor agrees with the front end.
3. Swap `hero.php` for the archetype.
4. Add the direction to the roster above with palette, type, layout, hero, rules, good-for.
5. Add a line to the log.

## Log
| Date | Client | Direction | Notes |
|---|---|---|---|
| 2026-10 | huayhuash-trek.com | alpine | the original kit look |
| 2026-10 | c3mutualaid.com | workshop | first run of the directions system |
