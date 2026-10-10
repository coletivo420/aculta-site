/**
 * Prévia da busca no popup: espera 300 ms sem digitação e carrega o HTML da prévia da rota do Portal (mesmo host).
 * Só a resposta mais recente é exibida; falha mostra uma mensagem na própria caixa.
 */
(function (Drupal, once) {
  'use strict';

  const DELAY_MS = 300;

  Drupal.behaviors.acultaSearchPreview = {
    attach(context) {
      once('aculta-search-preview', 'input.aculta-search-preview-input', context).forEach((input) => {
        const box = input.form.querySelector('#aculta-search-preview');
        const url = input.dataset.previewUrl;
        let timer = null;
        let latest = 0;

        input.addEventListener('input', () => {
          clearTimeout(timer);
          timer = setTimeout(() => {
            const term = input.value.trim();
            const request = ++latest;
            fetch(`${url}?q=${encodeURIComponent(term)}`, { credentials: 'same-origin', cache: 'no-store' })
              .then((response) => {
                if (!response.ok) {
                  throw new Error(`HTTP ${response.status}`);
                }
                return response.text();
              })
              .then((html) => {
                if (request === latest && box) {
                  box.innerHTML = html;
                }
              })
              .catch(() => {
                if (request === latest && box) {
                  box.textContent = Drupal.t('Não foi possível atualizar a prévia agora.');
                }
              });
          }, DELAY_MS);
        });
      });
    },
  };
})(Drupal, once);
