#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
if [[ $# -ne 1 ]]; then
  echo "Uso: $0 <arquivo.sqlite>" >&2
  exit 2
fi
STATE="$1"
[[ "$STATE" = /* ]] || STATE="$ROOT/$STATE"
php "$ROOT/scripts/estados/estado.php" validate "$STATE"
