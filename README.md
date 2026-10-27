# wp-site-kit

Grater Digital's WordPress rebuild starter. See **PLAYBOOK.md** for the process; this file is the 60-second start.

```bash
# 1. new project
cp -r wp-site-kit my-client && cd my-client
#    edit wp-content/themes/gd-starter/config/site.php   (NAP, CTA, socials, WhatsApp on/off)
#    read DIRECTIONS.md → pick/write a direction; set 'direction' + 'fonts_url' in site.php
#    edit wp-content/themes/gd-starter/theme.json        (palette, fonts — match the direction)
#    edit assets/css/directions/<name>.css               (all taste lives here, not in main.css)
#    drop photos in wp-content/themes/gd-starter/assets/img/  (hero.jpg intro.jpg band.jpg + page heroes)
#    write content/site.json                             (pages, blocks, menus)
#    rewrite pattern-parts/*.php placeholder copy

# 2. run locally (needs php 8, git, node + playwright)
bin/dev-up.sh                     # http://localhost:8090  admin/admin
php ~/wp-rig/wp-cli.phar --allow-root --path=~/wp-rig/wp eval-file scripts/seed.php
node bin/shots.js                 # preview/shots/*.png

# 3. deploy
zip -r build.zip wp-content content scripts
# host: unzip -o build.zip && find wp-content -type d -exec chmod 755 {} \; && find wp-content -type f -exec chmod 644 {} \;
#       wp theme activate gd-starter && wp plugin activate gd-reviews gd-instagram
#       wp eval-file scripts/seed.php && rm -rf content scripts && wp cache flush
APIFY_TOKEN=… wp eval-file scripts/configure-reviews.php
APIFY_TOKEN=… IG_HANDLE=… wp eval-file scripts/configure-instagram.php
```

Rename the theme folder + text domain if you want a per-client name: `grep -rl gd-starter . | xargs sed -i 's/gd-starter/client-slug/g'` and `mv wp-content/themes/gd-starter wp-content/themes/client-slug`.

`wp-content/plugins/gd-events` is a lean event CPT (date/venue/RSVP, upcoming→past, schema). `optional/gd-treks` is a worked example of a domain CPT (repeater, gallery, map, accordion block, schema). Copy it when the client has "offerings" that need structured pages; it still contains trekking vocabulary.
