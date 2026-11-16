/* TV da entrada · rotação automática de telas, carrosséis, relógio, atualização e atalhos. */
(function () {
  const corpo = document.body;
  const TEMPO = +corpo.dataset.tempo;                 // duração padrão de cada tela (ms)
  const VERSAO = corpo.dataset.versao;                // versão do conteúdo no momento em que a página abriu
  const POLLING = (+corpo.dataset.polling || 20) * 1000;
  const slides = [...document.querySelectorAll('.slide')];
  const itens = [...document.querySelectorAll('nav .it')];
  const prog = document.getElementById('prog');
  const login = document.getElementById('login');
  let i = 0, pausado = corpo.dataset.login === '1', tmr;

  /* ---------- troca de telas ---------- */
  function mostrar(n) {
    const dur = +slides[n].dataset.dur || TEMPO;
    slides.forEach(s => s.classList.remove('ativo'));
    slides[n].classList.add('ativo');
    itens.forEach(x => x.classList.toggle('on', x.dataset.m === slides[n].dataset.m));
    prog.style.transition = 'none';
    prog.style.width = '0';
    requestAnimationFrame(() => requestAnimationFrame(() => {
      prog.style.transition = 'width ' + dur + 'ms linear';
      prog.style.width = '100%';
    }));
    iniciaCarrossel(slides[n]);
    clearTimeout(tmr);
    tmr = setTimeout(proximo, dur);
  }

  function proximo() {
    if (pausado) { tmr = setTimeout(proximo, 1000); return; }
    i++;
    if (i >= slides.length) { location.reload(); return; }   // recarrega para pegar conteúdo novo e reinicia o loop
    mostrar(i);
  }

  /* ---------- carrosséis (retomam de onde pararam no ciclo anterior) ---------- */
  const carrosseis = [...document.querySelectorAll('.car')].map(el => {
    const trk = el.querySelector('.trk');
    const pgs = [...trk.children];
    let idx = 0;
    try {
      const salvo = localStorage.getItem(el.dataset.key);
      if (salvo !== null) idx = (parseInt(salvo, 10) + 1) % pgs.length;
    } catch (e) { /* sem armazenamento: começa do início */ }
    const c = { el, trk, pgs, idx, ms: +el.dataset.ms, timer: null, slide: el.closest('.slide') };
    irPara(c, false);
    return c;
  });

  function irPara(c, animar) {
    c.trk.style.transition = animar ? '' : 'none';
    c.trk.style.transform = 'translateX(-' + (c.idx * 100) + '%)';
    c.pgs.forEach((p, k) => p.classList.toggle('on', k === c.idx));
    const cnt = c.slide.querySelector('.cnt');
    if (cnt) cnt.textContent = (c.idx + 1) + ' / ' + c.pgs.length;
    try { localStorage.setItem(c.el.dataset.key, c.idx); } catch (e) { /* ignora */ }
  }

  function iniciaCarrossel(slide) {
    carrosseis.forEach(c => {
      clearInterval(c.timer);
      if (c.slide === slide && c.pgs.length > 1) {
        c.timer = setInterval(() => { c.idx = (c.idx + 1) % c.pgs.length; irPara(c, true); }, c.ms);
      }
    });
  }

  /* ---------- relógio ---------- */
  function relogio() {
    const d = new Date();
    document.getElementById('h').textContent = d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('dt').textContent = d.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' });
  }

  /* ---------- atualização: recarrega quando entra, sai ou expira algum conteúdo ---------- */
  setInterval(() => {
    if (pausado) return;
    fetch(corpo.dataset.urlVersao, { cache: 'no-store' })
      .then(r => r.json())
      .then(j => { if (j.v && j.v !== VERSAO) location.reload(); })
      .catch(() => { /* sem rede: tenta de novo no próximo ciclo */ });
  }, POLLING);

  /* ---------- atalhos: F = tela cheia · L = login da equipe · Esc = fechar login ---------- */
  document.addEventListener('keydown', e => {
    if (['INPUT', 'TEXTAREA'].includes(e.target.tagName) && e.key !== 'Escape') return;
    const k = e.key.toLowerCase();
    if (k === 'f') {
      if (!document.fullscreenElement) { if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen(); }
      else document.exitFullscreen();
    } else if (k === 'l' && login && !login.classList.contains('aberto')) {
      login.classList.add('aberto'); corpo.classList.add('login-aberto'); pausado = true;
      login.querySelector('input').focus(); e.preventDefault();
    } else if (e.key === 'Escape' && login) {
      login.classList.remove('aberto'); corpo.classList.remove('login-aberto'); pausado = false;
    }
  });

  relogio();
  setInterval(relogio, 15000);
  mostrar(0);
})();
