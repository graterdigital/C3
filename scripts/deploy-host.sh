#!/usr/bin/env bash
# Run ON the Kinsta box from the site's public/ directory after build.zip has been uploaded there:
#   cd ~/public && bash scripts/deploy-host.sh
# Idempotent — safe to re-run after uploading a newer build.zip.
set -euo pipefail
cd "$(dirname "$0")/.."

echo "== unzip"
unzip -o -q build.zip
find wp-content/themes/gd-starter wp-content/plugins/gd-events wp-content/plugins/gd-instagram -type d -exec chmod 755 {} \;
find wp-content/themes/gd-starter wp-content/plugins/gd-events wp-content/plugins/gd-instagram -type f -exec chmod 644 {} \;

echo "== activate"
wp theme activate gd-starter
wp plugin activate gd-events gd-instagram
wp plugin deactivate hello akismet 2>/dev/null || true
wp rewrite structure '/%postname%/' --hard

echo "== seed"
wp eval-file scripts/seed.php

echo "== settings"
wp option update blog_public 0          # keep noindex until launch; flip to 1 at go-live
wp option update timezone_string America/New_York
wp option update default_comment_status closed
wp option update uploads_use_yearmonth_folders 1

echo "== cleanup"
rm -rf content scripts build.zip
wp cache flush
wp kinsta cache purge --all 2>/dev/null || true
echo "Done → $(wp option get home)"
