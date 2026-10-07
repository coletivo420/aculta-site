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
})(Drupal, once);
