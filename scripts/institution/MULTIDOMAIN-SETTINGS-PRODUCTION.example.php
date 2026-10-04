<?php

// Add this allowlist to the production settings.php during controlled deploy.
// Never replace it with a wildcard subdomain pattern.
$settings['trusted_host_patterns'] = [
  '^aculta\.org$',
  '^conta\.aculta\.org$',
  '^apoio\.aculta\.org$',
  '^revista\.aculta\.org$',
  '^wiki\.aculta\.org$',
  '^loja\.aculta\.org$',
  '^cursos\.aculta\.org$',
];

// Drupal's settings.php already loads sites/default/services.yml. That tracked
// file sets the production session cookie domain to .aculta.org.
