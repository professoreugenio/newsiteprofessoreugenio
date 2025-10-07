<!-- Modal de Aviso de Atividades Pendentes -->
<?php if ($codigoUser != 1): ?>
    <div class="modal fade modal-pendencias" id="modalAtividadesPendentes" tabindex="-1" aria-labelledby="modalAtividadesPendentesLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAtividadesPendentesLabel">
                        <i class="bi bi-clipboard-x-fill"></i>
                        Atenção às suas atividades
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="pulse-dot"></span>
                        <span>Você possui <span id="qtdPendencias" class="badge badge-soft rounded-pill">0</span> atividade(s) pendente(s) em aulas já assistidas.</span>
                    </div>

                    <small class="text-muted d-block mb-2">Revisite as lições e conclua as atividades para manter seu progresso completo.</small>

                    <!-- Lista breve (até 3) com títulos das pendências, preenchida via JS -->
                    <ul id="listaPendencias" class="listinha"></ul>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-light btn-cta" data-bs-dismiss="modal">
                        Depois eu vejo
                    </button>
                    <button type="button" class="btn btn-warning text-dark btn-cta" id="btnIrParaPendencias">
                        Ver atividades pendentes
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Só prossegue se o modal existir na página
        const modalEl = document.getElementById('modalAtividadesPendentes');
        if (!modalEl) return;

        // Captura pendências
        const pendentes = document.querySelectorAll('.atividade-pendente');
        const qtd = pendentes.length;

        if (qtd > 0) {
            // Atualiza contador
            const qtdSpan = document.getElementById('qtdPendencias');
            if (qtdSpan) qtdSpan.textContent = qtd;

            // Monta lista (até 3 itens)
            const ul = document.getElementById('listaPendencias');
            if (ul) {
                ul.innerHTML = '';
                pendentes.forEach((el, i) => {
                    if (i > 2) return; // limita a 3
                    // tenta pegar o título da lição próxima
                    let titulo = '';
                    const tituloEl = el.closest('.licao')?.querySelector('.titulo-licao');
                    if (tituloEl) titulo = tituloEl.textContent.trim();
                    if (!titulo) titulo = 'Atividade pendente';

                    const li = document.createElement('li');
                    li.textContent = titulo;
                    ul.appendChild(li);
                });
            }

            // CTA que leva até a primeira pendência
            const btnIr = document.getElementById('btnIrParaPendencias');
            if (btnIr) {
                btnIr.addEventListener('click', () => {
                    // Troca para a aba "Aulas Assistidas" (se existir o botão)
                    const btnAssistidas = document.getElementById('btnAssistidas');
                    const btnNaoAssistidas = document.getElementById('btnNaoAssistidas');
                    if (btnAssistidas && btnNaoAssistidas) {
                        btnAssistidas.click();
                    }
                    // Fecha modal
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();

                    // Rola até a primeira pendência
                    setTimeout(() => {
                        const alvo = document.querySelector('.atividade-pendente');
                        if (alvo) {
                            // Busca container da lição para rolar melhor
                            const cont = alvo.closest('.licao') || alvo;
                            cont.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            // Breve destaque
                            cont.classList.add('shadow', 'border', 'border-warning', 'rounded-3');
                            setTimeout(() => {
                                cont.classList.remove('shadow', 'border', 'border-warning', 'rounded-3');
                            }, 1800);
                        }
                    }, 250);
                });
            }

            // Exibe modal
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>


<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verifica se há atividades pendentes entre as aulas assistidas
        let atividadesPendentes = document.querySelectorAll('.atividade-pendente');

        if (atividadesPendentes.length > 0) {
            let modal = new bootstrap.Modal(document.getElementById('modalAtividadesPendentes'));
            modal.show();
        }
    });
</script> -->