# Homelab ACULTA

Homelab is a development environment. Its normal database is the mutable SQLite
Runtime restored from `estados/`; production remains MariaDB. Keep the private
files directory and uploaded assets outside SQLite. Never deploy an Estado.

Fresh workstation flow:

```sh
git clone git@github.com:coletivo420/aculta-site.git
cd aculta-site
composer install
cp web/sites/default/settings.homelab.php.example web/sites/default/settings.homelab.php
export ACULTA_ENV=homelab
./scripts/estados/restaurar-estado.sh estados/2026-10-04_aculta_estado_fase8-integral-v1.sqlite
php vendor/drush/drush/drush.php status
php vendor/drush/drush/drush.php config:status
php vendor/drush/drush/drush.php updatedb:status
```

The local `settings.php` must include `settings.homelab.php` after its base
database configuration. Keep that loader change local/ignored; the bootstrap
script refuses to continue unless Drupal reports the expected SQLite Runtime.

The state contains integral data and must only be fetched from the private
repository. The production database and settings are separate MariaDB assets.
