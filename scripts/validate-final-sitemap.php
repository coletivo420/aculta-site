<?php
/** Windows-safe native generation: avoids Drush's shell-based batch subprocess. */
if (!in_array(\Drupal::request()->getHost(), ['localhost', '127.0.0.1'], TRUE)) throw new RuntimeException('Local only.');
$generator = \Drupal::service('simple_sitemap.generator');
$generator->rebuildQueue()->generate(\Drupal\simple_sitemap\Queue\QueueWorker::GENERATE_TYPE_BACKEND);
$xml = simplexml_load_string($generator->getContent());
if (!$xml) throw new RuntimeException('Invalid sitemap XML.');
$xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$urls = array_map('strval', $xml->xpath('//s:loc'));
foreach ($urls as $url) if (!str_starts_with($url, 'https://aculta.org/') || str_contains($url, '/node/12')) throw new RuntimeException('Unexpected sitemap URL.');
file_put_contents(dirname(__DIR__) . '/tmp/final-sitemap-urls.json', json_encode($urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo count($urls) . " official HTTPS sitemap URLs generated using the native backend API: OK\n";
