 <!-- Cabeçalho com curso -->
 <div class="mb-4 p-4 border rounded text-center bg-secondary bg-opacity-10">
     <h2 class="mb-2" style="color:#00BB9C;"><?= htmlspecialchars($nomeCurso) ?></h2>

 </div>

 <!-- Formulário de inscrição -->
 <form id="formInscricao" class="p-4 border rounded bg-secondary bg-opacity-10" novalidate>
     <div class="mb-3">
         <label for="nome" class="form-label">Nome completo</label>
         <input type="text" id="nome" name="nome" class="form-control" required>
         <input type="hidden" name="Codigochave" value="<?= htmlspecialchars($Codigochave) ?>">
         <input type="hidden" name="idCurso" value="<?= htmlspecialchars($enIdCurso) ?>">
         <div class="invalid-feedback">Informe seu nome completo.</div>
     </div>

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

 <script>
     document.addEventListener("DOMContentLoaded", () => {
         const form = document.getElementById("formInscricao");
         if (!form) return;

         form.addEventListener("submit", function(e) {
             e.preventDefault();

             // Validação HTML5
             if (!form.checkValidity()) {
                 form.classList.add("was-validated");
                 return;
             }

             const formData = new FormData(form);

             // Desabilita botão enquanto envia
             const btn = form.querySelector('[type="submit"]');
             const originalText = btn ? btn.innerHTML : "";
             if (btn) {
                 btn.disabled = true;
                 btn.innerHTML =
                     '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
             }

             fetch("vendasv1.0/ajaxIncricaoCurso.php", {
                     method: "POST",
                     body: formData,
                 })
                 .then(async (res) => {
                     let data;
                     try {
                         data = await res.json();
                     } catch (e) {
                         throw new Error("Resposta inválida do servidor");
                     }
                     return data;
                 })
                 .then((res) => {
                     if (res.status === "ok") {
                         window.location.href = "pagina_vendasPlano.php";
                     } else if (res.status === "ja_inscrito") {
                         window.location.href = res.redirect || "pagina_vendasPlano.php";
                     } else {
                         alert(res.mensagem || "Erro ao enviar inscrição.");
                     }
                 })
                 .catch((err) => {
                     console.error(err);
                     alert("Erro de comunicação com o servidor.");
                 })
                 .finally(() => {
                     if (btn) {
                         btn.disabled = false;
                         btn.innerHTML = originalText;
                     }
                 });
         });
     });
 </script>