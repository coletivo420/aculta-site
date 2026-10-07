#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

DRUSH=(php vendor/drush/drush/drush.php)
BASE_URL="${ACULTA_ACCOUNT_BASE_URL:-https://conta.aculta.toca.net.br}"
APPLY=0
if [[ "${1:-}" == "--apply" ]]; then
  APPLY=1
elif [[ -n "${1:-}" ]]; then
  echo "Usage: $0 [--apply]" >&2
  exit 2
fi

fail() {
  echo "I18N FAIL: $*" >&2
  exit 1
}

info() {
  echo "I18N: $*"
}

[[ -f vendor/drush/drush/drush.php ]] || fail "Drush is not installed. Run composer install first."

DB_DRIVER="$("${DRUSH[@]}" php:eval 'print(\Drupal::database()->driver());')"
[[ "$DB_DRIVER" == "sqlite" ]] || fail "Refusing to run outside the Homelab SQLite runtime (driver=$DB_DRIVER)."

TRANSLATIONS_PATH="$("${DRUSH[@]}" php:eval 'print((string) \Drupal\locale\StreamWrapper\TranslationsStream::basePath());')"
[[ -n "$TRANSLATIONS_PATH" ]] || fail "translations:// base path could not be resolved."

if [[ "$TRANSLATIONS_PATH" == *"://"* ]]; then
  fail "translations:// still resolves to a stream URI ($TRANSLATIONS_PATH). Create/resolve the real directory first."
fi

if [[ ! -d "$TRANSLATIONS_PATH" ]]; then
  if (( APPLY )); then
    mkdir -p "$TRANSLATIONS_PATH"
  else
    fail "Translations directory does not exist: $TRANSLATIONS_PATH"
  fi
fi

[[ -w "$TRANSLATIONS_PATH" ]] || fail "Translations directory is not writable: $TRANSLATIONS_PATH"
info "translations:// -> $TRANSLATIONS_PATH (writable)"

if (( APPLY )); then
  info "Applying only the i18n/config keys owned by PR #63; no global config:import."

  "${DRUSH[@]}" pm:enable config_translation -y

  "${DRUSH[@]}" config:set language.entity.pt-br label 'Português (Brasil)' -y

  "${DRUSH[@]}" config:set captcha.settings langcode pt-br -y
  "${DRUSH[@]}" config:set captcha.settings title 'Verificação de segurança' -y
  "${DRUSH[@]}" config:set captcha.settings description 'Esta verificação confirma que você é uma pessoa e ajuda a impedir envios automáticos de spam.' -y
  "${DRUSH[@]}" config:set captcha.settings wrong_captcha_response_message 'A verificação de segurança não foi concluída corretamente. Tente novamente.' -y

  "${DRUSH[@]}" config:set captcha.captcha_point.user_login_form langcode pt-br -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_login_form captchaType 'turnstile/Turnstile' -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_login_form label 'Login de usuário' -y

  "${DRUSH[@]}" config:set captcha.captcha_point.user_pass langcode pt-br -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_pass captchaType 'turnstile/Turnstile' -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_pass label 'Recuperação de senha' -y

  "${DRUSH[@]}" config:set captcha.captcha_point.user_register_form langcode pt-br -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_register_form captchaType 'turnstile/Turnstile' -y
  "${DRUSH[@]}" config:set captcha.captcha_point.user_register_form label 'Cadastro de usuário' -y

  for obsolete in \
    captcha.captcha_point.contact_message_personal_form \
    captcha.captcha_point.node_activity_form \
    captcha.captcha_point.node_article_form \
    captcha.captcha_point.node_document_form \
    captcha.captcha_point.node_editorial_highlight_form \
    captcha.captcha_point.node_page_form \
    captcha.captcha_point.node_project_form
  do
    "${DRUSH[@]}" config:delete "$obsolete" -y >/dev/null 2>&1 || true
  done

  "${DRUSH[@]}" locale:check
  "${DRUSH[@]}" locale:update --langcodes=pt-br -y
  "${DRUSH[@]}" locale:import pt-br \
    web/modules/custom/aculta_portal/translations/aculta_portal.pt-br.po \
    --type=customized \
    --override=all
  "${DRUSH[@]}" cache:rebuild
fi

CONFIG_JSON="$("${DRUSH[@]}" php:eval '
$mh = \Drupal::moduleHandler();
$storage = \Drupal::service("config.storage");
$points = [];
foreach ($storage->listAll("captcha.captcha_point.") as $name) {
  $cfg = \Drupal::config($name);
  $points[$name] = [
    "status" => (bool) $cfg->get("status"),
    "langcode" => $cfg->get("langcode"),
    "captchaType" => $cfg->get("captchaType"),
  ];
}
ksort($points);
$role = \Drupal::config("user.role.authenticated")->get("permissions") ?? [];
print(json_encode([
  "config_translation" => $mh->moduleExists("config_translation"),
  "language_label" => \Drupal::config("language.entity.pt-br")->get("label"),
  "captcha_langcode" => \Drupal::config("captcha.settings")->get("langcode"),
  "captcha_title" => \Drupal::config("captcha.settings")->get("title"),
  "skip_captcha_count" => count(array_filter($role, static fn($p) => $p === "skip CAPTCHA")),
  "points" => $points,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
')"

php -r '
$d = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
$fail = static function(string $m): never { fwrite(STDERR, "I18N FAIL: $m\n"); exit(1); };
if (!$d["config_translation"]) $fail("config_translation is disabled");
if ($d["language_label"] !== "Português (Brasil)") $fail("pt-br language label mismatch");
if ($d["captcha_langcode"] !== "pt-br") $fail("captcha.settings langcode mismatch");
if ($d["captcha_title"] !== "Verificação de segurança") $fail("captcha.settings title mismatch");
if ($d["skip_captcha_count"] !== 1) $fail("authenticated must contain exactly one skip CAPTCHA permission");
if (count($d["points"]) !== 5) $fail("expected exactly 5 CAPTCHA points, got " . count($d["points"]));
foreach ($d["points"] as $name => $point) {
  if (!$point["status"]) $fail("$name is disabled");
  if ($point["langcode"] !== "pt-br") $fail("$name is not pt-br");
  if ($point["captchaType"] !== "turnstile/Turnstile") $fail("$name is not Turnstile");
}
' "$CONFIG_JSON"

info "Active config invariants: PASS"

TMP_DIR="/tmp/aculta-i18n-pages"
rm -rf "$TMP_DIR"
mkdir -p "$TMP_DIR"

curl -fsSL "$BASE_URL/entrar" -o "$TMP_DIR/entrar.html"
curl -fsSL "$BASE_URL/recuperar-senha" -o "$TMP_DIR/recuperar-senha.html"

for page in entrar recuperar-senha; do
  grep -qiE '<html[^>]+lang="pt-br"' "$TMP_DIR/$page.html" || fail "$page does not render lang=pt-br"
done

grep -Fq 'Entrar' "$TMP_DIR/entrar.html" || fail "/entrar: Portuguese login title/button not found"
grep -Fq 'Senha' "$TMP_DIR/entrar.html" || fail "/entrar: Portuguese password label not found"
grep -Fq 'Redefinir sua senha' "$TMP_DIR/recuperar-senha.html" || fail "/recuperar-senha: Portuguese title not found"
grep -Fq 'Nome de usuário ou endereço de e-mail' "$TMP_DIR/recuperar-senha.html" || fail "/recuperar-senha: Portuguese identifier label not found"
grep -Fq 'Enviar' "$TMP_DIR/recuperar-senha.html" || fail "/recuperar-senha: Portuguese submit label not found"

for english in \
  'Log in' \
  'Enter the password that accompanies your username.' \
  'Reset your password' \
  'Username or email address' \
  'Password reset instructions will be sent to your registered email address.' \
  'Further instructions have been sent to your email address.'
do
  if grep -Fq "$english" "$TMP_DIR/entrar.html" "$TMP_DIR/recuperar-senha.html"; then
    fail "Visible/runtime English string remains: $english"
  fi
done

info "Anonymous GET rendering: PASS"

EXPORT_DIR="/tmp/aculta-i18n-config"
rm -rf "$EXPORT_DIR"
"${DRUSH[@]}" config:export --destination="$EXPORT_DIR" -y >/dev/null

info "Configuration Sync drift summary (informational; unrelated drift does not fail this i18n gate):"
diff -qr config/sync "$EXPORT_DIR" || true

"${DRUSH[@]}" updatedb:status

echo "I18N RUNTIME PASS (GET/config/Locale)."
echo "Manual interactive gate still required only for the post-submit password-reset privacy message behind Turnstile."
