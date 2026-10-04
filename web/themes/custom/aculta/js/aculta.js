/**
 * @file
 * Progressive enhancement of the main navigation, including Drupal AJAX.
 */
(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.acultaNavigation = {
    attach(context) {
      if (typeof bootstrap === 'undefined') {
        return;
      }
      once('aculta-navigation', '.aculta-navbar', context).forEach((navbar) => {
        const toggle = navbar.querySelector('.aculta-menu-toggle');
        const menu = navbar.querySelector('#aculta-primary-menu');
        if (!toggle || !menu) {
          return;
        }
        navbar.classList.add('aculta-navigation-ready');
        menu.addEventListener('keydown', (event) => {
          if (event.key === 'Escape' && !window.matchMedia('(min-width: 1100px)').matches) {
            bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false }).hide();
            toggle.focus();
          }
        });
      });
    },
  };

  // VVJB 2.0 has no focus pause. Use its public API; no custom slide timer.
  Drupal.behaviors.acultaEditorialFocus = {
    attach(context) {
      const pauseWhenFocused = (carousel) => {
        // VVJ hydrates lazily. The native pause API needs initialized markup.
        if (carousel.contains(document.activeElement) && carousel.querySelector('.vvjb-inner')?.dataset.vvjbInitialized === 'true') {
          carousel.pause?.();
        }
      };
      once('aculta-editorial-ready', document.documentElement).forEach(() => {
        document.addEventListener('vvj:ready', (event) => {
          const carousel = event.detail?.source;
          if (carousel?.matches('.view-home-editorial-highlights vvjb-carousel')) {
            pauseWhenFocused(carousel);
          }
        });
      });
      once('aculta-editorial-focus', '.view-home-editorial-highlights vvjb-carousel', context).forEach((carousel) => {
        // VVJ 2.0 returns the browser's ViewTransition without observing ready.
        // Rapid navigation intentionally skips an older transition. Handle that
        // cancellation locally; all slide logic and animation remain native.
        if (typeof carousel.withTransition === 'function') {
          const nativeTransition = carousel.withTransition.bind(carousel);
          carousel.withTransition = (callback) => {
            const transition = nativeTransition(callback);
            transition?.ready.catch((error) => {
              if (error.name !== 'AbortError') {
                throw error;
              }
            });
            return transition;
          };
        }
        carousel.addEventListener('focusin', () => pauseWhenFocused(carousel));
        // Keep it paused after keyboard interaction until the visitor presses Play.
        // This also avoids resuming a deliberately paused or reduced-motion carousel.
      });
    },
  };
})(Drupal, once);
