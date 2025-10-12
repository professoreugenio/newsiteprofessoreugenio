 <!-- Cabeçalho com curso -->
 <div class="mb-4 p-4 border rounded text-center bg-secondary bg-opacity-10">
     <h2 class="mb-2" style="color:#00BB9C;"><?= htmlspecialchars($nomeCurso) ?></h2>

 </div>

 <!-- Formulário de inscrição -->
 <?php
    // Resultados da consulta
    $horamanha = $rwTurma['horadem'] ?? '';
    $horatarde = $rwTurma['horadet'] ?? '';
    $horanoite = $rwTurma['horaden'] ?? '';

    // Validação individual (vazio ou 00:00:00 = inválido)
    $temManha = !empty($horamanha) && $horamanha !== '00:00:00';
    $temTarde = !empty($horatarde) && $horatarde !== '00:00:00';
    $temNoite = !empty($horanoite) && $horanoite !== '00:00:00';
    $temAlgum = $temManha || $temTarde || $temNoite;

    // Para controlar o "required" apenas no primeiro rádio renderizado (quando não houver "tarde")
    $requiredSet = false;
    ?>

 <?php $dec = encrypt($_COOKIE['nav'], $action = 'd');
    $exp = explode("&", $dec);
    $chaveAf =  $_GET['af'] ?? '-'; ?>
 <form id="formInscricao" class="p-4 border rounded bg-secondary bg-opacity-10" novalidate>
     <div class="mb-3">
         <label for="nome" class="form-label">Nome completo</label>
         <input type="text" id="nome" name="nome" class="form-control" required>
         <input type="hidden" name="Codigochave" value="<?= htmlspecialchars($Codigochave) ?>">
         <input type="hidden" name="idCurso" value="<?= htmlspecialchars($enIdCurso) ?>">
         <div class="invalid-feedback">Informe seu nome completo.</div>
     </div>

     <input type="hidden" name="chaveAf" id="chaveAf" value="<?= htmlspecialchars($chaveAf ?? '1000') ?>">

     <div class="mb-3">
         <label for="email" class="form-label">E-mail</label>
         <input type="email" id="email" name="email" class="form-control" required>
         <div class="invalid-feedback">Informe um e-mail válido.</div>
     </div>

     <div class="mb-3">
         <label for="telefone" class="form-label">Telefone / WhatsApp</label>
         <input type="tel" id="telefone" name="telefone" class="form-control" required>
         <div class="invalid-feedback">Informe seu número com DDD.</div>
     </div>

     <?php if ($temAlgum): ?>
         <div class="mb-3 text-center">
             <label class="form-label d-block mb-3">Selecione o horário do curso:</label>
             <div class="d-flex justify-content-center gap-3 flex-wrap">

                 <?php if ($temManha): ?>
                     <label class="form-check bg-dark text-light p-3 rounded shadow-sm">
                         <input
                             class="form-check-input me-2"
                             type="radio"
                             name="horario"
                             value="<?= htmlspecialchars($horamanha) ?>"
                             <?= (!$temTarde && !$requiredSet) ? 'required' : '' ?>>
                         <span class="fw-bold text-warning">Manhã </span>
                         <span class="text-decundary"><?= htmlspecialchars(substr($horamanha, 0, 5)) ?></span>
                     </label>
                     <?php $requiredSet = $requiredSet || (!$temTarde); ?>
                 <?php endif; ?>

                 <?php if ($temTarde): ?>
                     <label class="form-check bg-dark text-light p-3 rounded shadow-sm">
                         <input
                             class="form-check-input me-2"
                             type="radio"
                             name="horario"
                             value="<?= htmlspecialchars($horatarde) ?>"
                             checked
                             required>
                         <span class="fw-bold text-warning">Tarde </span>
                         <span class="text-decundary"><?= htmlspecialchars(substr($horatarde, 0, 5)) ?></span>
                     </label>
                     <?php $requiredSet = true; ?>
                 <?php endif; ?>

                 <?php if ($temNoite): ?>
                     <label class="form-check bg-dark text-light p-3 rounded shadow-sm">
                         <input
                             class="form-check-input me-2"
                             type="radio"
                             name="horario"
                             value="<?= htmlspecialchars($horanoite) ?>"
                             <?= (!$requiredSet) ? 'required' : '' ?>>
                         <span class="fw-bold text-warning">Noite </span>
                         <span class="text-decundary"><?= htmlspecialchars(substr($horanoite, 0, 5)) ?></span>
                     </label>
                     <?php $requiredSet = true; ?>
                 <?php endif; ?>

             </div>
             <div class="invalid-feedback d-block">Selecione um horário para continuar.</div>
         </div>
     <?php endif; ?>

     <div class="text-end mt-4">
         <button type="submit" class="btn btn-personalizado w-100">
             Continuar para pagamento <i class="bi bi-arrow-right-circle ms-2"></i>
         </button>
     </div>

     <div class="text-muted text-center mt-3 small">
         <i class="bi bi-shield-lock-fill me-1"></i> Seus dados estão protegidos e seguros
     </div>
 </form>



 <script>
     // Validação e localStorage
     const form = document.getElementById('formInscricao');

     ['nome', 'email', 'telefone'].forEach(id => {
         const input = document.getElementById(id);
         // Preencher campos salvos
         input.value = localStorage.getItem(id) || '';
         // Salvar mudanças
         input.addEventListener('input', () => {
             localStorage.setItem(id, input.value);
         });
     });

     // Validação Bootstrap
     form.addEventListener('submit', function(event) {
         if (!form.checkValidity()) {
             event.preventDefault();
             event.stopPropagation();
         } else {
             // Em produção: redirecionar para próxima etapa
             // location.href = 'formas_pagamento.php';
         }
         form.classList.add('was-validated');
     }, false);
 </script>

 <!-- <script>
     document.getElementById('formInscricao').addEventListener('submit', function(e) {
         e.preventDefault();

         const form = this;
         const formData = new FormData(form);

         // Validação HTML5
         if (!form.checkValidity()) {
             form.classList.add('was-validated');
             return;
         }

         fetch('vendasv1.0/ajaxIncricaoCurso.php', {
                 method: 'POST',
                 body: formData
             })
             .then(() => {

                 // Redireciona após 20s
                 setTimeout(() => {
                     window.location.href = 'pagina_vendasPlano.php';
                 }, 3000);
             })
             .catch(() => {
                 alert('Erro de comunicação com o servidor.');
             });
     });
 </script> -->



 <!-- <script>
     document.getElementById('formInscricao').addEventListener('submit', function(e) {
         e.preventDefault();

         const form = this;
         const formData = new FormData(form);

         // Validação HTML5
         if (!form.checkValidity()) {
             form.classList.add('was-validated');
             return;
         }

         fetch('vendasv1.0/ajaxIncricaoCurso.php', {
                 method: 'POST',
                 body: formData
             })
             .then(res => res.json())
             .then(res => {
                 if (res.status === 'ok') {
                     window.location.href = 'pagina_vendasPlano.php';
                 } else if (res.status === 'ja_inscrito') {
                     window.location.href = res.redirect; 
                 } else {
                     alert(res.mensagem || 'Erro ao enviar inscrição.');
                 }
             })
             .catch(() => {
                 alert('Erro de comunicação com o servidor.');
             });
     });
 </script> -->

 <!-- MODAL: Enviando inscrição -->
 <div class="modal fade" id="modalInscricao" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content bg-dark text-white border-0 rounded-4 shadow-lg">
             <div class="modal-header border-0">
                 <h5 class="modal-title">
                     <i class="bi bi-cloud-upload me-2"></i>
                     Processando sua inscrição
                 </h5>
             </div>
             <div class="modal-body">
                 <div class="d-flex align-items-center gap-3 mb-3">
                     <div class="spinner-border" role="status" aria-hidden="true"></div>
                     <div>
                         <div class="fw-semibold">Enviando dados com segurança…</div>
                         <small class="text-muted">Você será redirecionado em <span id="countdownSegundos">20</span>s.</small>
                     </div>
                 </div>

                 <div class="progress" style="height: 8px;">
                     <div class="progress-bar" id="progressBarInscricao" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-label="Progresso de redirecionamento"></div>
                 </div>

                 <div id="mensagemExtra" class="mt-3 small text-secondary">
                     Não feche esta janela. Estamos preparando a próxima etapa.
                 </div>
             </div>
         </div>
     </div>
 </div>


 <script>
     (function() {
         const form = document.getElementById('formInscricao');
         if (!form) return;

         const redirectUrl = 'pagina_vendasPlano.php'; // ajuste se quiser dinamizar via input hidden

         // Refs do modal e elementos internos
         const modalEl = document.getElementById('modalInscricao');
         let modalInstance = null;
         let timerId = null,
             intervalId = null;

         function abrirModal() {
             // Inicializa Modal Bootstrap 5 (backdrop estático via data-attrs no HTML)
             modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
             modalInstance.show();
         }

         function iniciarContagem20sEProgress() {
             const countdownSpan = document.getElementById('countdownSegundos');
             const progressBar = document.getElementById('progressBarInscricao');

             const totalMs = 3000;
             const stepMs = 100; // atualização a cada 100ms
             let elapsed = 0;

             // Atualiza segundos visuais 1x/segundo
             let segundosRestantes = 3;
             countdownSpan.textContent = segundosRestantes;
             intervalId = setInterval(() => {
                 segundosRestantes = Math.max(0, segundosRestantes - 1);
                 countdownSpan.textContent = segundosRestantes;
             }, 1000);

             // Barra de progresso suave
             timerId = setInterval(() => {
                 elapsed += stepMs;
                 const pct = Math.min(100, (elapsed / totalMs) * 100);
                 progressBar.style.width = pct.toFixed(2) + '%';
                 if (elapsed >= totalMs) {
                     clearInterval(timerId);
                     clearInterval(intervalId);
                     window.location.href = redirectUrl;
                 }
             }, stepMs);
         }

         function mostrarErro(msg) {
             // Troca conteúdo do modal para estado de erro (mantendo backdrop)
             const titulo = modalEl.querySelector('.modal-title');
             const body = modalEl.querySelector('.modal-body');

             titulo.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Falha ao enviar';
             body.innerHTML = `
        <p class="mb-3">Não foi possível concluir sua inscrição agora.</p>
        <div class="alert alert-warning border-0">
          ${msg || 'Erro de comunicação com o servidor.'}
        </div>
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-light" id="btnFecharErro">Fechar</button>
        </div>
      `;

             // Permite fechar no erro
             modalEl.removeAttribute('data-bs-backdrop');
             modalEl.removeAttribute('data-bs-keyboard');

             modalEl.querySelector('#btnFecharErro').addEventListener('click', () => {
                 bootstrap.Modal.getInstance(modalEl)?.hide();
             });
         }

         form.addEventListener('submit', function(e) {
             e.preventDefault();

             // Validação HTML5
             if (!form.checkValidity()) {
                 form.classList.add('was-validated');
                 return;
             }

             const formData = new FormData(form);

             // Abre modal imediatamente (feedback instantâneo)
             abrirModal();

             fetch('vendasv1.0/ajaxIncricaoCurso.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then((res) => {
                     // Considera qualquer 2xx como ok; se quiser checar, use res.ok
                     if (!res.ok) throw new Error('Resposta inválida do servidor.');
                     // Inicia contador e barra de progresso
                     iniciarContagem20sEProgress();
                 })
                 .catch((err) => {
                     clearInterval(timerId);
                     clearInterval(intervalId);
                     mostrarErro(err?.message);
                 });
         });
     })();
 </script>