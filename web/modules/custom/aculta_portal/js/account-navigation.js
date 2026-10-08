(function (Drupal, once) {
  'use strict';

  const mergeSettings = (responseDocument) => {
    const settingsElement = responseDocument.querySelector('script[data-drupal-selector="drupal-settings-json"]');
    if (!settingsElement || !window.drupalSettings) return;
    const merge = (target, source) => {
      Object.entries(source).forEach(([key, value]) => {
        if (value && typeof value === 'object' && !Array.isArray(value)) {
          target[key] = merge(target[key] && typeof target[key] === 'object' ? target[key] : {}, value);
        }
        else target[key] = value;
      });
      return target;
    };
    merge(window.drupalSettings, JSON.parse(settingsElement.textContent));
  };

  const requestDocument = async (url) => {
    const target = new URL(url, window.location.href);
    if (target.origin !== window.location.origin) {
      throw new Error('Cross-origin account AJAX is not allowed.');
    }
    const response = await fetch(target.href, {
      credentials: 'same-origin',
      headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) throw new Error('Account page request failed.');
    return new DOMParser().parseFromString(await response.text(), 'text/html');
  };

  const setTopLevelActive = (layout) => {
    layout.querySelectorAll('a[data-aculta-portal-link]').forEach((link) => {
      const href = new URL(link.href);
      const active = href.pathname === window.location.pathname || (href.pathname === '/dados' && window.location.pathname.startsWith('/dados/'));
      if (active) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
      link.classList.toggle('is-active', active);
    });
  };

  const updateTitle = (responseDocument) => {
    if (responseDocument.title) document.title = responseDocument.title;
  };

  const updatePanel = async (url, pushState) => {
    const layout = document.querySelector('[data-aculta-account-layout]');
    const panel = layout?.querySelector('[data-aculta-account-content]');
    const status = layout?.querySelector('[data-aculta-account-status]');
    const loading = layout?.querySelector('[data-aculta-account-loading]');
    if (!layout || !panel) {
      window.location.assign(url);
      return;
    }

    layout.setAttribute('aria-busy', 'true');
    panel.setAttribute('aria-busy', 'true');
    if (loading) loading.hidden = false;
    if (status) status.textContent = Drupal.t('Carregando conteúdo da seção.');
    try {
      const responseDocument = await requestDocument(url);
      const replacement = responseDocument.querySelector('[data-aculta-account-content]');
      if (!replacement) throw new Error('Account content missing from response.');
      Drupal.detachBehaviors(panel, window.drupalSettings || {}, 'unload');
      panel.innerHTML = replacement.innerHTML;
      updateTitle(responseDocument);
      if (pushState) window.history.pushState({ acultaAccount: true }, '', url);
      setTopLevelActive(layout);
      mergeSettings(responseDocument);
      Drupal.attachBehaviors(panel, window.drupalSettings || {});
      const heading = panel.querySelector('h2');
      if (heading) {
        heading.setAttribute('tabindex', '-1');
        heading.focus();
      }
      if (status) status.textContent = Drupal.t('@section carregado.', { '@section': heading?.textContent || '' });
    }
    catch (error) {
      window.location.assign(url);
    }
    finally {
      layout.setAttribute('aria-busy', 'false');
      panel.setAttribute('aria-busy', 'false');
      if (loading) loading.hidden = true;
    }
  };

  const updateDataPanel = async (url, pushState) => {
    const layout = document.querySelector('[data-aculta-account-layout]');
    const dataPanel = layout?.querySelector('[data-aculta-account-data-content]');
    const status = layout?.querySelector('[data-aculta-account-status]');
    if (!layout || !dataPanel) {
      await updatePanel(url, pushState);
      return;
    }
    dataPanel.setAttribute('aria-busy', 'true');
    if (status) status.textContent = Drupal.t('Carregando seus dados.');
    try {
      const responseDocument = await requestDocument(url);
      const replacement = responseDocument.querySelector('[data-aculta-account-data-content]');
      if (!replacement) throw new Error('Account data content missing from response.');
      Drupal.detachBehaviors(dataPanel, window.drupalSettings || {}, 'unload');
      dataPanel.innerHTML = replacement.innerHTML;
      updateTitle(responseDocument);
      if (pushState) window.history.pushState({ acultaAccount: true, acultaAccountData: true }, '', url);
      layout.querySelectorAll('a[data-aculta-account-data-link]').forEach((link) => {
        const active = new URL(link.href).pathname === window.location.pathname;
        if (active) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
        link.classList.toggle('is-active', active);
      });
      setTopLevelActive(layout);
      mergeSettings(responseDocument);
      Drupal.attachBehaviors(dataPanel, window.drupalSettings || {});
      const heading = dataPanel.querySelector('h3');
      if (heading) {
        heading.setAttribute('tabindex', '-1');
        heading.focus();
      }
      if (status) status.textContent = Drupal.t('@section carregada.', { '@section': heading?.textContent || '' });
    }
    catch (error) {
      window.location.assign(url);
    }
    finally {
      dataPanel.setAttribute('aria-busy', 'false');
    }
  };

  Drupal.behaviors.acultaPortalNavigation = {
    attach(context) {
      once('aculta-portal-navigation', '[data-aculta-account-layout]', context).forEach((layout) => {
        layout.addEventListener('click', (event) => {
          const nestedLink = event.target.closest('a[data-aculta-account-data-link]');
          const portalLink = event.target.closest('a[data-aculta-portal-link]');
          const link = nestedLink || portalLink;
          if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
          const target = new URL(link.href, window.location.href);
          // Cross-purpose links are normal browser navigation, never AJAX.
          if (target.origin !== window.location.origin) return;
          if (target.pathname === window.location.pathname) {
            event.preventDefault();
            return;
          }
          event.preventDefault();
          if (nestedLink) updateDataPanel(link.href, true);
          else updatePanel(link.href, true);
        });
        window.addEventListener('popstate', () => {
          const pathname = window.location.pathname;
          if (pathname.startsWith('/dados')) updateDataPanel(window.location.href, false);
          else updatePanel(window.location.href, false);
        });
      });
    },
  };

  Drupal.behaviors.acultaPortalPhotoEditor = {
    attach(context) {
      once('aculta-portal-photo-editor', '[data-aculta-photo-editor]', context).forEach((editor) => {
        const dialog = editor.querySelector('[data-aculta-photo-dialog]');
        const trigger = editor.querySelector('[data-aculta-photo-open]');
        const closeButton = editor.querySelector('[data-aculta-photo-close]');
        const heading = dialog?.querySelector('#aculta-account-photo-title');
        if (!dialog || !trigger || typeof dialog.showModal !== 'function') return;

        // The dialog starts open so its form remains available without JS.
        editor.classList.add('is-enhanced');
        if (dialog.open) dialog.close();

        trigger.addEventListener('click', () => {
          if (!dialog.open) dialog.showModal();
          window.requestAnimationFrame(() => heading?.focus());
        });
        closeButton?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
          if (event.target === dialog) dialog.close();
        });
        dialog.addEventListener('close', () => trigger.focus());
      });
    },
  };
})(Drupal, once);
