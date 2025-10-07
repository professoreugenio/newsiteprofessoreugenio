
    (function(){
  const SET_COOKIE_URL = 'regixv3.0/setCookie.php';
    const INSERE_URL     = 'regixv3.0/regexInsereAcessos.php';
    const FINALIZA_URL   = 'regixv3.0/ajax_finalizaAcesso.php';

    function regixEnsureCookie(){
    return fetch(SET_COOKIE_URL, {
        method: 'POST',
    credentials: 'same-origin',
    headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.ok?r.json():{ok:false}).catch(()=>({ok:false}));
  }

    function regixInsereAcesso(){
    return fetch(INSERE_URL, {
        method: 'POST',
    credentials: 'same-origin',
    headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.ok?r.json():{ok:false}).catch(()=>({ok:false}));
  }

    function regixFinalizaAcesso(){
    try{
      if (navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify({ })], {type:'application/json'});
    navigator.sendBeacon(FINALIZA_URL, blob);
      } else {
        fetch(FINALIZA_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            keepalive: true,
            body: JSON.stringify({})
        });
      }
    } catch(e){ }
  }

    function start(){
        regixEnsureCookie().then(ck => {
            if (ck && ck.ok) regixInsereAcesso();
        });
  }

    // Garante que o DOM já está pronto antes de rodar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
        start();
  }

    // Finalização segura
    window.addEventListener('pagehide', regixFinalizaAcesso, {once:false});
    document.addEventListener('visibilitychange', function(){
    if (document.visibilityState === 'hidden') regixFinalizaAcesso();
  });
})();

