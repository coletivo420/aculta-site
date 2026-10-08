#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
RUNTIME="$ROOT/var/database/aculta-runtime.sqlite"
if [[ $# -ne 2 ]]; then
  echo "Uso: $0 <marco> <versao> (ex.: fase8-integral v1)" >&2
  exit 2
fi
MILESTONE="$1"
VERSION="$2"
if [[ ! "$MILESTONE" =~ ^[a-z0-9-]+$ || ! "$VERSION" =~ ^v[0-9]+$ ]]; then
  echo "Marco/versão inválidos." >&2
  exit 2
fi
if [[ ! -f "$RUNTIME" ]]; then
  echo "Runtime SQLite ausente: $RUNTIME" >&2
  exit 1
fi
DATE="$(date +%F)"
NAME="${DATE}_aculta_estado_${MILESTONE}-${VERSION}.sqlite"
DEST="$ROOT/estados/$NAME"
if [[ -e "$DEST" ]]; then
  echo "Estado já existe; Estados são imutáveis: $DEST" >&2
  exit 1
fi
php -r '
require $argv[1] . "/vendor/autoload.php";
$db = class_exists(\Pdo\Sqlite::class) ? new \Pdo\Sqlite("sqlite:" . $argv[2]) : new PDO("sqlite:" . $argv[2]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if (method_exists($db, "createCollation")) { $db->createCollation("NOCASE_UTF8", [Drupal\Component\Utility\Unicode::class, "strcasecmp"]); }
else { $db->sqliteCreateCollation("NOCASE_UTF8", [Drupal\Component\Utility\Unicode::class, "strcasecmp"]); }
$db->exec("PRAGMA wal_checkpoint(TRUNCATE)");
$db->exec("PRAGMA journal_mode=DELETE");
$db->exec("VACUUM");
foreach (["quick_check", "integrity_check"] as $test) { if ($db->query("PRAGMA " . $test)->fetchColumn() !== "ok") { fwrite(STDERR, "SQLite integrity check failed.\n"); exit(1); } }
' "$ROOT" "$RUNTIME"
cp -- "$RUNTIME" "$DEST"
chmod 0444 "$DEST"
METADATA="$(php -r '
$db = new PDO("sqlite:" . $argv[1], null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$statement = $db->prepare("SELECT name FROM sqlite_master WHERE type = ? AND name NOT LIKE ?");
$statement->execute(["table", "sqlite_%"]);
$tables = $statement->fetchAll(PDO::FETCH_COLUMN);
$rows = 0;
foreach ($tables as $table) { $q = "\"" . str_replace("\"", "\"\"", $table) . "\""; $rows += (int) $db->query("SELECT COUNT(*) FROM " . $q)->fetchColumn(); }
echo count($tables) . " " . $rows;
' "$DEST")"
read -r TABLE_COUNT ROW_COUNT <<< "$METADATA"
SHA256="$(sha256sum "$DEST" | awk '{print $1}')"
DRUPAL_VERSION="$(php -r '$lock=json_decode(file_get_contents($argv[1]),true); foreach(array_merge($lock["packages"]??[], $lock["packages-dev"]??[]) as $package){if(($package["name"]??"")==="drupal/core"){echo $package["version"];exit;}}' "$ROOT/composer.lock")"
CREATED_AT="$(date --iso-8601=seconds)"
cat >> "$ROOT/estados/manifesto.yml" <<YAML
  - file: $NAME
    milestone: $MILESTONE
    drupal: $DRUPAL_VERSION
    database: sqlite
    fidelity: integral
    table_count: $TABLE_COUNT
    row_count: $ROW_COUNT
    sha256: $SHA256
    created_at: $CREATED_AT
YAML
php "$ROOT/scripts/estados/estado.php" validate "$DEST"
echo "Estado copiado sem sanitização: $NAME"
