# Security Review - Phase 9

Date: 2026-10-05

This document records the disposition of the Phase 9 security review without
storing credentials or personal data.

## Current disposition

| Finding | Disposition |
| --- | --- |
| Turnstile account forms | RESOLVED |
| Shared ACCOUNT/COURSES session | RESOLVED |
| Cross-domain logout | RESOLVED |
| Domain isolation | RESOLVED |
| SQLite functional/concurrency checks | RESOLVED |
| Webform Default text format | ACCEPTED_KNOWN |
| Custom Views access | RESOLVED |
| UID 1 active | PREPRODUCTION_CONTROL |
| Runtime PHP directory permissions | RESOLVED_WITH_ENVIRONMENT_POLICY |
| Drush private .htaccess warning | ACCEPTED_HOMELAB_CLI |

## UID 1

UID 1 remains active because no alternate administrator has yet been validated.
It must not be blocked blindly.

Drupal 10.3+ exposes the super-user access policy through
`security.enable_super_user`. Before production, create/validate at least one
separate administrator with the required permissions, verify login and recovery,
then disable UID 1 or disable the super-user policy according to the final
production security decision.

This is a pre-production control, not a reason to create a second hidden
administrator in code or to store credentials in Git.

## Webform Default

`webform_default` is treated as an internal Webform format. The Phase 9 review
does not mutate it merely to silence Security Review.

## Runtime files

The Homelab uses Nginx/PHP-FPM and a controlled ACL model. Drupal's
`file_chmod_directory` and `file_chmod_file` are documented in the Homelab
settings example to prevent regenerated cache directories from becoming 0777.

The private files directory remains restricted to the PHP-FPM runtime user.
A Drush CLI warning about creating Apache `.htaccess` there is accepted on the
Homelab because the private directory is outside the public webroot and Nginx
does not consume `.htaccess`. Production uses Apache and must independently
validate private-files protection.

## Production gates

Before production launch:

1. validate a non-UID1 administrator;
2. decide and apply UID1/super-user hardening;
3. validate Apache private/public files protection;
4. configure real Turnstile/SMTP/OAuth/payment secrets outside Git;
5. rerun Security Review and application smoke tests.
