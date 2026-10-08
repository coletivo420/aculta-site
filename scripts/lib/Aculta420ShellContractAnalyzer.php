<?php

declare(strict_types=1);

/**
 * Static analyzer for the ACULTA420 Domain Presentation shell boundary.
 */
final class Aculta420ShellContractAnalyzer {

  /**
   * @return string[]
   *   Contract violations.
   */
  public static function analyze(string $themeRoot): array {
    $findings = [];
    $hookPath = $themeRoot . '/src/Hook/ThemeHooks.php';
    $pagePath = $themeRoot . '/templates/page.html.twig';

    if (!is_file($hookPath)) {
      $findings[] = 'ThemeHooks.php is missing.';
      return $findings;
    }
    if (!is_file($pagePath)) {
      $findings[] = 'page.html.twig is missing.';
      return $findings;
    }

    $hookSource = (string) file_get_contents($hookPath);
    $pageSource = (string) file_get_contents($pagePath);

    foreach ([
      "\$variables['domain_presentation']['identity']" => 'ThemeHooks must consume domain_presentation.identity.',
      'buildDomainBrandFallback' => 'ThemeHooks must derive a minimal domain branding fallback before Twig.',
      'aculta_header_has_content' => 'ThemeHooks must expose explicit renderable-header presence.',
    ] as $needle => $message) {
      if (!str_contains($hookSource, $needle)) {
        $findings[] = $message;
      }
    }

    foreach ([
      'aculta_domain_brand_fallback.home_url',
      'aculta_domain_brand_fallback.label',
    ] as $needle) {
      if (!str_contains($pageSource, $needle)) {
        $findings[] = 'page.html.twig is missing identity consumer: ' . $needle;
      }
    }

    if (!str_contains($pageSource, '{% if aculta_header_has_content %}')
      || !str_contains($pageSource, '{% elseif aculta_domain_brand_fallback %}')) {
      $findings[] = 'Existing Drupal header must remain primary and domain identity fallback-only.';
    }

    if (preg_match(
      '/\b(?:wiki|courses|shop|support|account|magazine|main)\b\s*(?:==|!=|===|!==)/i',
      $pageSource,
    )) {
      $findings[] = 'Twig must not branch on a concrete Domain purpose.';
    }
    foreach ([
      'regions.brand_media',
      'regions.navigation',
      'regions.actions',
      "['regions']['brand_media']",
      "['regions']['navigation']",
      "['regions']['actions']",
    ] as $needle) {
      if (str_contains($hookSource, $needle) || str_contains($pageSource, $needle)) {
        $findings[] = 'B.4 must not consume deferred region: ' . $needle;
      }
    }

    $forbiddenPatterns = [
      'DomainInterface' => '/\bDomainInterface\b/',
      'DomainPurposeManager' => '/\bDomainPurposeManager\b/',
      'Domain negotiator' => '/domain\.negotiator|DomainNegotiator/i',
      'Portal service' => '/aculta_portal\./',
      'service locator' => '/\\Drupal::(?:service|entityTypeManager|request)\s*\(/',
      'hostname decision' => '/getHost\s*\(|HTTP_HOST|SERVER_NAME|\.aculta\.org|\.toca\.net\.br/i',
    ];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($themeRoot, FilesystemIterator::SKIP_DOTS)) as $file) {
      if (!$file->isFile() || !in_array(strtolower($file->getExtension()), ['php', 'twig', 'js'], TRUE)) {
        continue;
      }
      $source = (string) file_get_contents($file->getPathname());
      $relative = str_replace($themeRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
      if (strtolower($file->getExtension()) === 'php') {
        $tokens = token_get_all($source);
        $source = '';
        foreach ($tokens as $token) {
          if (is_array($token)) {
            if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], TRUE)) {
              continue;
            }
            $source .= $token[1];
          }
          else {
            $source .= $token;
          }
        }
      }
      elseif (strtolower($file->getExtension()) === 'twig') {
        $source = preg_replace('/\{#.*?#\}/s', '', $source) ?? $source;
      }
      foreach ($forbiddenPatterns as $label => $pattern) {
        if (preg_match($pattern, $source)) {
          $findings[] = $label . ' is forbidden in theme runtime source: ' . $relative;
        }
      }
      if (preg_match(
        '/purpose[^\n;]{0,80}(?:==|!=|===|!==|match\s*\(|case\s+)[^\n;]{0,80}[\'"](?:wiki|courses|shop|support|account|magazine|main)[\'"]/i',
        $source,
      )) {
        $findings[] = 'Concrete purpose branching is forbidden in theme runtime source: ' . $relative;
      }
    }

    return array_values(array_unique($findings));
  }

}
