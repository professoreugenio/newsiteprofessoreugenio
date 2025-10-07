<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<!-- Conteúdo -->

<section id="Corpo" class="">
    <div class="container text-center">

        <!-- Listagem de Turmas -->
        <div class="container">

            <div class="text-center mb-5">

                <h4 class="mt-4 mb-2 text-white">
                    <i class="bi bi-layers"></i> Edtar Perfil
                </h4>


            </div>
            <div class="cards-container">

                <?php if ($codigoUser == 1): ?>
                    <?php // require 'config_curso1.0/ListaModulos2.0.php'; 
                    ?>
                    <?php require 'perfilv2.0/sobreocurso.php';
                    ?>
                <?php else: ?>
                    <?php // require 'config_curso1.0/ListaModulos2.0.php'; 
                    ?>
                    <?php require 'perfilv2.0/sobreocurso.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<!-- Modal: Aulas Atuais / Assistidas -->
<!-- Modal: Aulas Atuais / Assistidas -->

<script>
    // Clique no botão de atualização
    document.getElementById('btnupdate').addEventListener('click', function() {
        const form = document.getElementById('idformupdate');
        const btn = this;

        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Atualizando...';

        fetch('perfilv2.0/updateperfil.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                showToast(res.mensagem, res.sucesso ? 'success' : 'danger');
                if (res.sucesso) {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                }
            })
            .catch(() => {
                showToast('Erro ao atualizar. Tente novamente.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Atualizar <i class="bi bi-save"></i>';
            });
    });

    // Toast Bootstrap personalizado
    function showToast(mensagem, tipo = 'info') {
        const toastContainer = document.getElementById('toast-container') || criarContainerToast();
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${mensagem}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
        toastContainer.appendChild(toastEl);
        setTimeout(() => toastEl.remove(), 4000);
    }

    function criarContainerToast() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.AOS) AOS.init({
            duration: 700,
            once: true
        });

        // Abra automaticamente ao carregar (remova se não quiser auto-show)
        const el = document.getElementById('modalAulasAtuais');
        if (el && window.bootstrap) {
            const modal = new bootstrap.Modal(el);
            modal.show();
        }
    });
</script>


<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>
<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>