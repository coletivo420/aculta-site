#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"
[[ -f var/database/aculta-runtime.sqlite ]] || { echo "SQLite Runtime is missing." >&2; exit 1; }
php scripts/estados/estado.php validate-runtime var/database/aculta-runtime.sqlite
DB_DRIVER="$(php vendor/drush/drush/drush.php php:eval 'print(\Drupal::database()->driver());')"
DB_PATH="$(php vendor/drush/drush/drush.php php:eval 'print(\Drupal::database()->getConnectionOptions()["database"]);')"
EXPECTED_DB="$(realpath var/database/aculta-runtime.sqlite)"
[[ "$DB_DRIVER" == "sqlite" && "$(realpath "$DB_PATH")" == "$EXPECTED_DB" ]] || {
  echo "Drupal is not connected to the expected SQLite Runtime." >&2
  exit 1
}
php vendor/drush/drush/drush.php status
php vendor/drush/drush/drush.php updatedb:status
php vendor/drush/drush/drush.php config:status
