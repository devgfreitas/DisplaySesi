/* Painel, consulta e cardápio · comportamentos da interface (sem JavaScript dentro das views). */
(function () {
  /* confirmação antes de enviar formulários e antes de clicar em botões marcados com data-confirm */
  document.addEventListener('submit', function (e) {
    var msg = e.target.dataset.confirm;
    if (msg && !confirm(msg)) e.preventDefault();
  });
  document.addEventListener('click', function (e) {
    var b = e.target.closest('button[data-confirm]');
    if (b && !confirm(b.dataset.confirm)) e.preventDefault();
    if (e.target.closest('[data-print]')) window.print();
  });

  /* formulário de publicação: mostra só os campos que fazem sentido para o tipo escolhido */
  var tipo = document.getElementById('tipo');
  if (tipo) {
    var tv = document.getElementById('tv');
    var alvoTurmas = document.querySelector('input[name=publico_alvo][value=turmas]');
    var ajusta = function () {
      var t = tipo.value;
      if (['recado', 'prova', 'tarefa', 'recado_individual'].indexOf(t) >= 0) alvoTurmas.checked = true;
      document.getElementById('boxProva').classList.toggle('oculto', ['prova', 'tarefa', 'recado'].indexOf(t) < 0);
      document.getElementById('boxTurmas').classList.toggle('oculto', !alvoTurmas.checked);
      document.getElementById('boxTv').classList.toggle('oculto', !tv.checked);
    };
    document.querySelectorAll('input[name=publico_alvo]').forEach(function (x) { x.addEventListener('change', ajusta); });
    tipo.addEventListener('change', ajusta);
    tv.addEventListener('change', ajusta);
    ajusta();
  }
})();
