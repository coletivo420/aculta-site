#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"
command -v php >/dev/null || { echo "PHP CLI is required." >&2; exit 1; }
php -m | grep -qi '^pdo_sqlite$' || { echo "PHP pdo_sqlite is required." >&2; exit 1; }
[[ -f web/sites/default/settings.homelab.php ]] || {
  echo "Create the ignored Homelab settings file from settings.homelab.php.example and configure trusted hosts/private files." >&2
  exit 1
}
[[ -f vendor/autoload.php ]] || composer install
[[ -f var/database/aculta-runtime.sqlite ]] || {
  echo "Restore an Estado first with scripts/estados/restaurar-estado.sh." >&2
  exit 1
}
DB_DRIVER="$(php vendor/drush/drush/drush.php php:eval 'print(\Drupal::database()->driver());')"
DB_PATH="$(php vendor/drush/drush/drush.php php:eval 'print(\Drupal::database()->getConnectionOptions()["database"]);')"
EXPECTED_DB="$(realpath var/database/aculta-runtime.sqlite)"
[[ "$DB_DRIVER" == "sqlite" && "$(realpath "$DB_PATH")" == "$EXPECTED_DB" ]] || {
  echo "Refusing bootstrap: Drupal is not connected to var/database/aculta-runtime.sqlite." >&2
  exit 1
}
./scripts/homelab/verify-aculta-homelab.sh
echo "Bootstrap checks complete. Apache is the canonical Homelab baseline; review config/update status before any mutation."
