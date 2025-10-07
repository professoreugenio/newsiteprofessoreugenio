<!-- Modal Aviso de Aniversário (Colegas) -->
<div class="modal fade" id="modalAvisoAniversario" tabindex="-1" aria-hidden="true" aria-labelledby="labelAvisoAniversario">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-0" style="border:0; overflow:hidden; border-radius:18px;">
            <!-- Cabeçalho colorido -->
            <div class="p-4" style="background: linear-gradient(135deg, #FF9C00, #E52E71); color:#fff;">
                <h5 id="labelAvisoAniversario" class="m-0 fw-bold">🎉 <?= $nmUser; ?></h5>
                <small class="opacity-75"><?= $sub; ?></small>
            </div>

            <!-- Corpo -->
            <div class="modal-body py-4 px-4">
                <?php if ($temAviso): ?>
                    <p class="mb-3" style="color: #E52E71;">Esses colegas estão de aniversário hoje:</p>
                    <ul class="list-group text-start shadow-sm" style="border-radius:12px;">
                        <?php foreach ($AvisoAniversariante as $i => $row): ?>
                            <li class="list-group-item d-flex align-items-center">
                                <span class="me-2">🎂</span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($row['nome_aluno'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <small class="text-muted">
                                        Turma: <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars($row['nome_turma'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </small>
                                </div>
                                <!-- Botão opcional: copiar mensagem padrão -->
                                <button type="button"
                                    class="btn btn-outline-primary btn-sm ms-2"
                                    onclick="copiarParabens('<?= htmlspecialchars($row['nome_aluno'] ?? '', ENT_QUOTES, 'UTF-8'); ?>')">
                                    Copiar mensagem
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="mt-3 small ">
                        Dica: mande um “feliz aniversário” no grupo! 🥳
                    </div>
                
                <?php endif; ?>
            </div>

            <!-- Rodapé -->
            <div class="modal-footer border-0 d-flex justify-content-center pb-4">
                <button type="button" class="btn btn-light fw-semibold px-4" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($temAviso): ?>
    <script>
        // Exibir só uma vez por dia por turma
        document.addEventListener('DOMContentLoaded', function() {
            const chaveTurma = "<?= htmlspecialchars($chaveturmaUser ?? '', ENT_QUOTES, 'UTF-8'); ?>";
            const hoje = "<?= date('Ymd'); ?>";
            const storageKey = `aviso_parabens_${chaveTurma}_${hoje}`;

            if (!localStorage.getItem(storageKey)) {
                const modal = new bootstrap.Modal(document.getElementById('modalAvisoAniversario'));
                modal.show();
                localStorage.setItem(storageKey, '1');
            }
        });

        // Copiar mensagem padrão para a área de transferência
        function copiarParabens(nome) {
            const msg = `Feliz aniversário, ${nome}! 🎉🎂 Muitas alegrias e sucesso!`;
            navigator.clipboard.writeText(msg).then(() => {
                // Toast simples no centro
                const div = document.createElement('div');
                div.textContent = 'Mensagem copiada!';
                div.style.position = 'fixed';
                div.style.top = '50%';
                div.style.left = '50%';
                div.style.transform = 'translate(-50%, -50%)';
                div.style.background = 'rgba(0,0,0,.8)';
                div.style.color = '#fff';
                div.style.padding = '10px 16px';
                div.style.borderRadius = '8px';
                div.style.zIndex = '9999';
                document.body.appendChild(div);
                setTimeout(() => div.remove(), 1200);
            });
        }
    </script>
<?php endif; ?>