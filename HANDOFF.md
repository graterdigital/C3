# C3 · Creative Collaboration for Compassion — build notes

Built from wp-site-kit on 2026-10-27. Live: https://c3collaborative.kinsta.cloud (→ c3mutualaid.com). Kinsta SSH: `c3collaborative@129.80.57.27 -p 46658`.
Local rig: `bin/dev-up.sh 8090` → `wp eval-file scripts/seed.php`. Admin: admin / admin.

## What's in the build

| Piece | Where | Notes |
|---|---|---|
| Theme | `wp-content/themes/gd-starter` v1.2.1 | Direction **workshop** (`assets/css/directions/workshop.css`): IBM Plex Sans, flyer navy `#0f1e38`, pantry red `#d32f2a`, highlighter `#f5d33f`, hard-edged tiles, zero radius. See `DIRECTIONS.md`. |
| Copy | `content/copy.json` | Every pattern string, keyed. Patterns read via `gd_copy()`. Seed copies it into `theme/content/` so it survives `rm -rf content` on the host. |
| Pages | `content/site.json` | Home (canvas), What we do, About, Get involved, Donate, FAQ, Contact, Updates (posts page) |
| Events | `wp-content/plugins/gd-events` | CPT `event`: start/end/all-day/venue/address/cost/organizer/link. Archive = upcoming then past. Google Calendar link, Event schema, `gd/events` block, `[gd_events]`. Seeded from `content/site.json` → `events[]`. |
| Forms | `inc/forms.php` | Native. Get involved page: Request help / Volunteer switcher (hash-driven, no reload). Entries → Dashboard → **Form entries** (private CPT, photo attachments) + email to `form_recipient` / contact email. Gravity Forms: set `gf_help_form_id` / `gf_volunteer_form_id` in `config/site.php`. |
| Partners | `config/site.php` → `partners[]` | Logo file or text chip. Home + About. |
| Images | `assets/img/`, manifest `content/images.json` | Seed imports all; re-imports when a file's md5 changes. |
| Instagram | gd-instagram | Not connected yet — needs `APIFY_TOKEN=… IG_HANDLE=c3_creativecollaborative wp eval-file scripts/configure-instagram.php`. Section hides itself until then. |

## Client copy round-trip (bin/copy-doc.py)

`python3 bin/copy-doc.py export` → `preview/C3-Website-Updates.docx`: every piece of copy as ID | Current | Your change, every image slot with a thumbnail and a paste-a-link column, events (+2 blank), partners (+3 blank), posts, and a free-form change-request table.
`python3 bin/copy-doc.py import edited.docx --dry` shows what the client changed; without `--dry` writes `content/copy.json`, `content/site.json`, `config/site.php`, downloads linked images (Drive/Dropbox links rewritten to direct download), crops them to the slot spec in `content/images.json`, and writes free-form requests to `content/CHANGE-REQUESTS.md`. Links the sandbox can't fetch: save them into a folder named `<slot>.<ext>` and re-run with `--images DIR`. Then seed + deploy.

## Deploy

```
zip -r update-x.y.z.zip wp-content content scripts
scp -P 46658 .\update-x.y.z.zip c3collaborative@129.80.57.27:public/build.zip
ssh -p 46658 c3collaborative@129.80.57.27 "cd public && unzip -o build.zip scripts/deploy-host.sh && bash scripts/deploy-host.sh 2>&1"
```
Bump `GD_THEME_VERSION` in `functions.php` and `Version:` in `style.css` so caches bust. Script sets `blog_public 0`; flip to 1 at launch.

## Still to do

1. Deploy output confirmation; hard-refresh check on Kinsta.
2. Apify token → Instagram feed.
3. SMTP plugin (entries are stored regardless, but email delivery from Kinsta is unreliable without it).
4. Domain c3mutualaid.com → Kinsta; `blog_public 1`; submit to Search Console.
5. Client confirmations: phone number (none shown), service area wording, pantry-build event date (seeded as draft), the founder story on About.
6. Partner logos still missing: Black Lion Party GA, Above the Rain Collective, Reclaimed Karma, Joyful Noise (slots exist in `content/images.json`).
