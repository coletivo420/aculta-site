/**
 * Botão de olho do painel "Credenciais do ambiente": mostra o valor completo sob demanda e
 * volta à máscara ao ser acionado de novo. O valor vem da rota protegida por token, nunca do HTML.
 */
(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    var button = event.target.closest('.aculta-secret-value__eye');
    if (!button) {
      return;
    }
    var cell = button.closest('.aculta-secret-value');
    var text = cell ? cell.querySelector('.aculta-secret-value__text') : null;
    if (!text) {
      return;
    }
    if (button.getAttribute('aria-pressed') === 'true') {
      text.textContent = text.getAttribute('data-masked');
      button.setAttribute('aria-pressed', 'false');
      return;
    }
    fetch(button.getAttribute('data-reveal-url'), { credentials: 'same-origin', cache: 'no-store' })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('indisponível');
        }
        return response.json();
      })
      .then(function (data) {
        text.textContent = data.value;
        button.setAttribute('aria-pressed', 'true');
      })
      .catch(function () {
        text.textContent = 'indisponível';
      });
  });
})();
