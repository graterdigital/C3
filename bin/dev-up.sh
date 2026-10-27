#!/usr/bin/env bash
# Local WordPress rig with SQLite (no MySQL needed) + WP-CLI, theme/plugins symlinked from this repo.
# Usage: bin/dev-up.sh [port]      then open http://localhost:8089  (admin/admin)
set -euo pipefail
PORT="${1:-8089}"
KIT="$(cd "$(dirname "$0")/.." && pwd)"
RIG="${RIG:-$HOME/wp-rig}"
WP="$RIG/wp"

mkdir -p "$RIG"; cd "$RIG"
[ -d wp ]        || git clone -q --depth 1 --branch 7.1.3 https://github.com/WordPress/WordPress.git wp
[ -d sqlite ]    || git clone -q --depth 1 --branch v2.2.9 https://github.com/WordPress/sqlite-database-integration.git sqlite
[ -f wp-cli.phar ] || curl -sSL -o wp-cli.phar https://github.com/wp-cli/wp-cli/releases/download/v2.11.0/wp-cli-2.11.0.phar
wp() { php "$RIG/wp-cli.phar" --allow-root --path="$WP" "$@"; }

P="$WP/wp-content/plugins/sqlite-database-integration"
if [ ! -f "$WP/wp-content/db.php" ]; then
  rm -rf "$P"; cp -r sqlite "$P"
  cp "$P/db.copy" "$WP/wp-content/db.php"
  sed -i "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$P#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" "$WP/wp-content/db.php"
fi

# symlink kit code into the rig
for t in "$KIT"/wp-content/themes/*;  do ln -sfn "$t" "$WP/wp-content/themes/$(basename "$t")"; done
for p in "$KIT"/wp-content/plugins/*; do ln -sfn "$p" "$WP/wp-content/plugins/$(basename "$p")"; done
for p in "$KIT"/optional/*;           do ln -sfn "$p" "$WP/wp-content/plugins/$(basename "$p")"; done

if [ ! -f "$WP/wp-config.php" ]; then
  wp config create --dbname=wp --dbuser=wp --dbpass=wp --skip-check --force --extra-php <<EOF
define('WP_DEBUG', true); define('WP_DEBUG_LOG', true); define('WP_DEBUG_DISPLAY', false);
define('WP_HOME','http://localhost:$PORT'); define('WP_SITEURL','http://localhost:$PORT'); define('DISABLE_WP_CRON', true);
EOF
  wp core install --url="http://localhost:$PORT" --title="Dev" --admin_user=admin --admin_password=admin --admin_email=dev@example.com --skip-email
  wp theme activate "$(basename "$(ls -d "$KIT"/wp-content/themes/* | head -1)")"
  wp plugin activate $(ls "$KIT"/wp-content/plugins | tr '\n' ' ') || true
  wp rewrite structure '/%postname%/'
fi

cd "$WP"
pkill -f "php -S 127.0.0.1:$PORT" 2>/dev/null || true
PHP_CLI_SERVER_WORKERS=4 nohup php -S 127.0.0.1:$PORT -t . >/dev/null 2>&1 &
sleep 1
echo "WordPress running at http://localhost:$PORT  (admin / admin)"
echo "Seed:   cd $KIT && php $RIG/wp-cli.phar --allow-root --path=$WP eval-file scripts/seed.php"
echo "Shots:  cd $KIT && NODE_PATH=\$(npm root -g) node bin/shots.js"
