<!-- Modal: Criar Chave de Afiliado -->
<!-- Modal de Confirmação -->
<!-- Modal de Confirmação -->
<div class="modal fade" id="modalChaveAfiliado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color:#2d095c; border-radius:12px;">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-coin me-2"></i> Criar sua filiação
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p class="fs-5 mb-3">Você ainda não possui uma chave de afiliado.</p>
                <p class="fw-bold fs-6 text-warning">Cadastre-se e comece a gerar renda hoje mesmo!</p>
                <div id="msgChaveFeedback" class="mt-3"></div>
            </div>

            <div class="modal-footer border-0 justify-content-center">
                <button class="btn px-4 py-2 fw-semibold" data-bs-dismiss="modal"
                    style="background-color:#6c757d; border:none; color:white; border-radius:6px;">
                    Cancelar
                </button>
                <button class="btn px-4 py-2 fw-semibold" id="btnConfirmarChave"
                    style="background-color:#ff7f2a; border:none; color:white; border-radius:6px;">
                    Confirmar
                </button>
            </div>

        </div>
    </div>
</div>


<script>
    (function() {
        const btn = document.getElementById('btnChaveAfiliado');
        const modalEl = document.getElementById('modalChaveAfiliado');
        const btnConfirmar = document.getElementById('btnConfirmarChave');
        const feedback = document.getElementById('msgChaveFeedback');
        let bsModal = null;

        if (modalEl && window.bootstrap) {
            bsModal = new bootstrap.Modal(modalEl);
        }

        function limpaFeedback(msg = '', ok = false) {
            feedback.className = '';
            if (!msg) {
                feedback.innerHTML = '';
                return;
            }
            feedback.classList.add('alert', ok ? 'alert-success' : 'alert-danger');
            feedback.innerHTML = msg;
        }

        // Verificação inicial
        btn.addEventListener('click', async () => {
            try {
                const r = await fetch('afiliadosv1.0/ajax_checkChaveAfiliado.php', {
                    method: 'POST'
                });
                const data = await r.json();

                if (data?.ok && data.possui) {
                    window.location.href = 'afiliados.php';
                } else if (bsModal) {
                    limpaFeedback('');
                    bsModal.show();
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao verificar sua chave de afiliado.');
            }
        });

        // Confirma criação
        btnConfirmar.addEventListener('click', async () => {
            try {
                btnConfirmar.disabled = true;
                limpaFeedback('Criando sua chave de afiliado...', true);

                const r = await fetch('afiliadosv1.0/ajax_criarChaveAfiliado.php', {
                    method: 'POST'
                });
                const data = await r.json();

                if (data?.ok) {
                    limpaFeedback('Chave criada com sucesso! Redirecionando...', true);
                    setTimeout(() => window.location.href = 'afiliados.php', 800);
                } else {
                    btnConfirmar.disabled = false;
                    limpaFeedback(data?.msg || 'Erro ao criar chave.', false);
                }
            } catch (e) {
                btnConfirmar.disabled = false;
                console.error(e);
                limpaFeedback('Erro de conexão.', false);
            }
        });
    })();
</script>