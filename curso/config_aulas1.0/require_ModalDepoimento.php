<!-- MODAL: Depoimento -->
<div class="modal fade" id="modalDepoimento" tabindex="-1" aria-labelledby="modalDepoimentoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Header -->
            <div class="modal-header py-3" style="background:#112240;">
                <h5 class="modal-title text-white d-flex align-items-center" id="modalDepoimentoLabel">
                    <i class="bi bi-chat-square-text me-2"></i> Depoimento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar" id="btnCloseModal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body p-4" style="background:#f7f9fb;">
                <form id="formDepoimento" novalidate>
                    <input type="hidden" name="iduser" value="<?= $codigoUser ?? '' ?>">

                    <!-- Título auxiliar -->
                    <div class="mb-2">
                        <span class="badge" style="background:#00BB9C;">Novo depoimento</span>
                    </div>

                    <!-- Depoimento -->
                    <div class="mb-3">
                        <label for="textoDepoimento" class="form-label fw-semibold" style="color:#112240;">Seu depoimento</label>
                        <textarea id="textoDepoimento" name="depoimento" class="form-control rounded-3" rows="4" maxlength="300" placeholder="Escreva aqui seu depoimento..." required></textarea>
                        <div class="d-flex justify-content-between mt-1 small">
                            <div class="form-text">Máximo de 300 caracteres.</div>
                            <div id="contagemDepo" class="text-muted">0/300</div>
                        </div>
                    </div>

                    <!-- Permissão -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="color:#112240;">
                            Permite que o professor possa divulgar sua opinião para valorizar seu trabalho?
                        </label>
                        <div class="d-flex gap-2">

                            <!-- Autorizo -->
                            <input type="radio" class="btn-check" name="permissao" id="permSim" value="1" autocomplete="off" required>
                            <label class="btn w-100 rounded-3 py-2 fw-semibold perm-option" for="permSim">
                                <i class="bi bi-check-circle me-1"></i> Autorizo
                            </label>

                            <!-- Não autorizo -->
                            <input type="radio" class="btn-check" name="permissao" id="permNao" value="0" autocomplete="off" required>
                            <label class="btn w-100 rounded-3 py-2 fw-semibold perm-option" for="permNao">
                                <i class="bi bi-x-circle me-1"></i> Não autorizo
                            </label>
                        </div>

                        <div class="form-text mt-1">Você pode alterar esta permissão posteriormente com o suporte.</div>
                        <div class="invalid-feedback d-block" id="permAviso" style="display:none;">
                            Confirme sua escolha de permissão para continuar.
                        </div>
                    </div>

                    <style>
                        /* Aparência base */
                        .perm-option {
                            border: 2px solid #ccc;
                            color: #555;
                            background: #f8f9fa;
                            transition: all .25s ease;
                            text-align: center;
                        }

                        .form-text {
                            font-size: 0.875em;
                            color: #061016ff;
                        }

                        .perm-option:hover {
                            filter: brightness(0.97);
                        }

                        /* Quando selecionado (verde destaque) */
                        .btn-check:checked+.perm-option {
                            background: #e9f9f1;
                            border-color: #28a745;
                            color: #28a745;
                            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, .25);
                        }
                    </style>



                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-between align-items-center" style="background:#ffffff;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="btnFecharProtegido" data-bs-dismiss="modal">
                    Fechar
                </button>
                <button type="submit" form="formDepoimento" class="btn btn-sm rounded-3 text-white" id="btnEnviarDepo" style="background:#00BB9C;">
                    <i class="bi bi-send-fill me-1"></i> Enviar
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    /* Hover states para as opções */
    .perm-option:hover {
        filter: brightness(0.98);
    }

    /* Quando checked, reforça a borda */
    .btn-check:checked+.perm-option {
        box-shadow: 0 0 0 0.25rem rgba(0, 187, 156, .15);
    }

    #btnEnviarDepo:hover {
        filter: brightness(0.95);
    }
</style>


<script>
    (function() {
        const form = document.getElementById('formDepoimento');
        const modalEl = document.getElementById('modalDepoimento');
        const btnX = document.getElementById('btnCloseModal');
        const btnFechar = document.getElementById('btnFecharProtegido');
        const permAviso = document.getElementById('permAviso');
        const contagem = document.getElementById('contagemDepo');
        const txt = document.getElementById('textoDepoimento');

        // contador de caracteres
        const max = 300;
        const updateCount = () => {
            contagem.textContent = `${txt.value.length}/${max}`;
        };
        txt.addEventListener('input', updateCount);
        updateCount();

        // flag: permissão escolhida?
        let permissaoEscolhida = false;

        // marca a escolha de permissão
        modalEl.addEventListener('change', (e) => {
            if (e.target && e.target.name === 'permissao') {
                permissaoEscolhida = true;
                permAviso.style.display = 'none';
                // habilita fechar, se quiser
                btnX.disabled = false;
                btnFechar.disabled = false;
            }
        });

        // no show: reseta travas
        modalEl.addEventListener('show.bs.modal', () => {
            permissaoEscolhida = false;
            permAviso.style.display = 'none';
            // impede fechar até escolher
            btnX.disabled = true;
            btnFechar.disabled = true;
        });

        // bloquear fechamento se não escolheu
        modalEl.addEventListener('hide.bs.modal', (ev) => {
            if (!permissaoEscolhida) {
                // Se ainda não escolheu, cancela
                ev.preventDefault();
                permAviso.style.display = 'block';
                // feedback visual rápido
                permAviso.classList.add('animate__animated', 'animate__shakeX');
                setTimeout(() => permAviso.classList.remove('animate__animated', 'animate__shakeX'), 600);
            }
        });

        // SUBMIT
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // valida permissão
            const perm = form.querySelector('input[name="permissao"]:checked');
            if (!perm) {
                permAviso.style.display = 'block';
                return;
            }

            const btn = e.submitter || document.querySelector('button[form="formDepoimento"][type="submit"]');
            const oldHTML = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            }

            const dados = new FormData(form);

            // idartigo obrigatório
            if (!dados.get('idartigo')) {
                dados.append('idartigo', '<?= $codigoaula ?? '' ?>');
            }

            fetch('depoimento1.0/ajax_salvarDepoimento.php', {
                    method: 'POST',
                    body: dados
                })
                .then(r => r.json())
                .then(res => {
                    if (res.sucesso) {
                        if (btn) btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Enviado!';
                        setTimeout(() => {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = oldHTML;
                            }
                            form.reset();
                            permissaoEscolhida = true; // libera fechamento
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modal.hide();
                        }, 700);
                    } else {
                        alert(res.mensagem || 'Erro ao enviar depoimento.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = oldHTML;
                        }
                    }
                })
                .catch(() => {
                    alert('Erro na requisição.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = oldHTML;
                    }
                });
        });
    })();
</script>