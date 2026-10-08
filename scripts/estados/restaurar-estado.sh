#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
if [[ $# -lt 1 || $# -gt 2 ]]; then
  echo "Uso: $0 <arquivo.sqlite> [runtime.sqlite]" >&2
  exit 2
fi
STATE="$1"
[[ "$STATE" = /* ]] || STATE="$ROOT/$STATE"
RUNTIME="${2:-$ROOT/var/database/aculta-runtime.sqlite}"
php "$ROOT/scripts/estados/estado.php" restore "$STATE" "$RUNTIME"
