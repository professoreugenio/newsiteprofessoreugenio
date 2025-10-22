<!-- TOPO: CHAVE DA TURMA -->
<div class="col-md-12 mt-4 mb-4">
    <label class="form-label fw-semibold">Chave da Turma</label>

    <div class="d-flex flex-wrap align-items-center gap-2">

        <!-- Gerar Chave -->
        <button type="button" class="btn btn-warning" id="btnGerarChave">
            <i class="bi bi-key me-1"></i> Gerar Chave
        </button>

        <!-- Campo + Mostrar/Ocultar -->
        <div class="input-group" style="max-width: 300px;">
            <input
                type="text"
                id="campoChave"
                class="form-control"
                readonly
                value="<?= htmlspecialchars($rwChave['chavesc'] ?? '••••••••') ?>"
                style="letter-spacing: 3px;"
                data-real-value="<?= htmlspecialchars($rwChave['chavesc'] ?? '') ?>">
            <button class="btn btn-outline-secondary" type="button" id="btnToggleChave" title="Mostrar/Ocultar">
                <i class="bi bi-eye" id="iconeOlho"></i>
            </button>
        </div>

        <!-- Copiar chave -->
        <button type="button" class="btn btn-outline-primary" id="btnCopiarChave">
            <i class="bi bi-clipboard-check me-1"></i> Copiar chave
        </button>

        <!-- Ver QR Code -->
        <button type="button" class="btn btn-outline-dark" id="btnQrChave" data-bs-toggle="modal" data-bs-target="#modalQrChave">
            <i class="bi bi-qr-code me-1"></i> Ver QR Code
        </button>

        <!-- Finalizar / Reativar -->
        <?php if ($andamento == '1'): ?>
            <button type="button" id="btnFinalizarTurma" class="btn btn-secondary btn-sm ms-2" data-acao="reativar">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reativar turma
            </button>
        <?php else: ?>
            <button type="button" id="btnFinalizarTurma" class="btn btn-danger btn-sm ms-2" data-acao="finalizar">
                <i class="bi bi-flag-fill me-1"></i> Finalizar turma
            </button>
        <?php endif; ?>

        <span id="respFinalizarTurma" class="ms-2 small"></span>
    </div>

    <!-- URL oculta usada no QRCode (não exibida) -->
    <input type="hidden" id="urlLoginComChave" value="">
</div>

<!-- MODAL: QR CODE DA CHAVE -->
<div class="modal fade" id="modalQrChave" tabindex="-1" aria-labelledby="modalQrChaveLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalQrChaveLabel">
                    <i class="bi bi-qr-code me-2"></i>QR Code de Acesso
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body d-flex flex-column align-items-center">
                <div id="qrContainer" class="p-2 border rounded"></div>
                <small class="text-muted mt-2">Aponte a câmera do celular para acessar o login com a chave.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- FORM PRINCIPAL -->
<form id="formEditarTurma" class="row g-4" method="post" data-aos="fade-up">

    <!-- CHECKBOXES -->
    <div class="col-md-12 mt-2">
        <div class="d-flex flex-wrap gap-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="visivelst" name="visivelst" value="1" <?= $chkon ?>>
                <label class="form-check-label" for="visivelst">Visível no site</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="comercialt" name="comercialt" value="1" <?= $chcom ?>>
                <label class="form-check-label" for="comercialt">Comercial</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="aovivo" name="aovivo" value="1" <?= $chvivo ?>>
                <label class="form-check-label" for="aovivo">Ao vivo</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="visiveltube" name="visiveltube" value="1" <?= $chkytube ?>>
                <label class="form-check-label" for="visiveltube">Visível no YouTube</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="institucional" name="institucional" value="1" <?= $institucional ?>>
                <label class="form-check-label" for="institucional">Institucional</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="andamento" name="andamento" value="1" <?= $chkanda ?>>
                <label class="form-check-label" for="andamento">Em andamento</label>
            </div>
        </div>
    </div>

    <input type="hidden" name="chave" value="<?= htmlspecialchars($ChaveTurma) ?>">

    <!-- SELECT PRODUTO AFILIADO -->
    <div class="col-md-4">
        <label for="produtoAfiliado" class="form-label">Produto Afiliado</label>
        <select class="form-select" id="produtoAfiliado" name="produtoafiliado">
            <option value="">Selecione um produto...</option>
            <?php
            try {
                $sql = "SELECT codigoprodutoafiliado, nomeap 
                FROM a_site_afiliados_produto 
                WHERE visivelap = 1 
                ORDER BY nomeap ASC";
                $stmt = $con->prepare($sql);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($row['codigoprodutoafiliado'] == $produtoafiliado) ? 'selected' : '';
                    echo '<option value="' . $row['codigoprodutoafiliado'] . '" ' . $selected . '>' . htmlspecialchars($row['nomeap']) . ' ' . $row['codigoprodutoafiliado'] . '</option>';
                }
            } catch (Exception $e) {
                echo '<option value="">Erro ao carregar produtos</option>';
            }
            ?>
        </select>
    </div>

    <div class="col-md-4">
        <label for="nomeTurma" class="form-label">Nome da Turma</label>
        <input type="text" class="form-control" id="nomeTurma" name="nometurma" value="<?= htmlspecialchars($Nometurma) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="pastaTurma" class="form-label">Pasta</label>
        <input type="text" class="form-control" id="pastaTurma" name="pasta" value="<?= htmlspecialchars($Pasta) ?>">
    </div>

    <div class="col-md-4">
        <label for="nomeProfessor" class="form-label">Nome do Professor</label>
        <input type="text" class="form-control" id="nomeProfessor" name="nomeprofessor" value="<?= htmlspecialchars($NomeProfessor) ?>">
    </div>

    <div class="col-md-4">
        <label for="celularprofessor" class="form-label">Celular do Professor</label>
        <input type="text" class="form-control" id="celularprofessor" name="celularprofessor" value="<?= htmlspecialchars($CelularProfessor) ?>">
    </div>

    <!-- HORÁRIOS -->

    <div class="col-12">
        <div class="d-flex align-items-center gap-2 mb-1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Data Início</label>
                    <input type="date" class="form-control" name="datainicio" value="<?= $datainiciost ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data Fim</label>
                    <input type="date" class="form-control" name="datafim" value="<?= $datafimst ?>">
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-alarm"></i><strong>Horários das Turmas</strong>
        </div>
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Manhã (De)</label>
                <input type="time" class="form-control" name="manha_de" value="<?= $horadem ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Manhã (Às)</label>
                <input type="time" class="form-control" name="manha_as" value="<?= $horaparam ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Tarde (De)</label>
                <input type="time" class="form-control" name="tarde_de" value="<?= $horadet ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tarde (Às)</label>
                <input type="time" class="form-control" name="tarde_as" value="<?= $horaparat ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Noite (De)</label>
                <input type="time" class="form-control" name="noite_de" value="<?= $horaden ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Noite (Às)</label>
                <input type="time" class="form-control" name="noite_as" value="<?= $horaparan ?>">
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <label for="bgcolor" class="form-label">Cor de Fundo</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor_cs" value="<?= htmlspecialchars($Bocolor) ?>">
    </div>

    <!-- WhatsApp (OPCIONAL) -->
    <div class="col-md-5">
        <label for="linkWhatsapp" class="form-label">Link do WhatsApp <span class="badge bg-secondary">Opcional</span></label>
        <input type="url" class="form-control" id="linkWhatsapp" name="linkwhatsapp" value="<?= htmlspecialchars($linkWhatsapp) ?>" placeholder="https://wa.me/5599999999999">
    </div>

    <div class="col-md-5">
        <label for="linkYoutube" class="form-label">Link do YouTube</label>
        <input type="url" class="form-control" id="linkYoutube" name="youtubesct" value="<?= htmlspecialchars($linkYoutube) ?>">
    </div>

    <div class="col-md-12">
        <label for="previa" class="form-label">Prévia</label>
        <textarea class="form-control" id="previa" name="previa" rows="3"><?= htmlspecialchars($previa) ?></textarea>
    </div>

    <div class="col-md-12">
        <label for="lead" class="form-label">Lead</label>
        <input type="text" class="form-control" id="lead" name="lead" value="<?= htmlspecialchars($lead) ?>">
    </div>

    <div class="col-md-12">
        <label for="detalhes" class="form-label">Detalhes</label>
        <textarea class="form-control" id="detalhes" name="detalhes" rows="3"><?= htmlspecialchars($detalhes) ?></textarea>
    </div>

    <div class="col-md-12">
        <label for="sobreocurso" class="form-label">Sobre o Curso</label>
        <textarea class="form-control" id="sobreocurso" name="sobreocurso" rows="4"><?= htmlspecialchars($sobreocurso) ?></textarea>
    </div>

    <div class="col-12 mt-2">
        <button type="submit" class="btn btn-success me-2">
            <i class="bi bi-save me-1"></i> Salvar Alterações
        </button>
        <a href="cursos_turmas.php?id=<?= $_GET['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</form>

<!-- QRCode.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<script>
    jQuery(function($) {

        // ------------------ Chave da Turma: gerar ------------------
        const chaveturmaRef = "<?= $ChaveTurma ?>";

        $('#btnGerarChave').on('click', function() {
            $.post('cursosv1.0/ajax_chaveTurma.php', {
                chaveturma: chaveturmaRef
            }, function(res) {
                try {
                    const data = JSON.parse(res);
                    if (data.sucesso) {
                        $('#campoChave').val(data.chave).data('real-value', data.chave);
                        $('#iconeOlho').removeClass('bi-eye-slash').addClass('bi-eye');
                        atualizarUrlOculta();
                    } else {
                        alert(data.mensagem || 'Não foi possível gerar a chave.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro ao gerar chave.');
                }
            });
        });

        // ------------------ Mostrar/Ocultar chave ------------------
        $('#btnToggleChave').on('click', function() {
            const campo = $('#campoChave');
            const realVal = campo.data('real-value') || '';
            const oculto = campo.val().includes('•');
            campo.val(oculto ? realVal : '••••••••');
            $('#iconeOlho').toggleClass('bi-eye bi-eye-slash');
        });

        // ------------------ Copiar chave ------------------
        $('#btnCopiarChave').on('click', async function() {
            const chave = ($('#campoChave').data('real-value') || '').toString().trim();
            if (!chave) {
                alert('Nenhuma chave disponível para copiar.');
                return;
            }
            try {
                await navigator.clipboard.writeText(chave);
                // feedback simples (pode trocar por toast)
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $(this).html('<i class="bi bi-clipboard-check me-1"></i> Copiado!');
                setTimeout(() => {
                    $('#btnCopiarChave').removeClass('btn-primary').addClass('btn-outline-primary')
                        .html('<i class="bi bi-clipboard-check me-1"></i> Copiar chave');
                }, 1800);
            } catch (err) {
                alert('Não foi possível copiar. Copie manualmente.');
            }
        });

        // ------------------ URL oculta para QR ------------------
        function atualizarUrlOculta() {
            const chave = ($('#campoChave').data('real-value') || '').toString().trim();
            const url = chave ?
                'https://professoreugenio.com/inscricao.php?key=' + encodeURIComponent(chave) :
                '';
            $('#urlLoginComChave').val(url);
        }
        atualizarUrlOculta(); // inicial

        // ------------------ Modal QR Code ------------------
        let qrInstance = null;

        $('#modalQrChave').on('shown.bs.modal', function() {
            const url = $('#urlLoginComChave').val();
            const box = document.getElementById('qrContainer');
            box.innerHTML = ''; // limpa

            if (!url) {
                box.innerHTML = '<div class="text-danger small">Gere ou revele uma chave válida para criar o QR Code.</div>';
                return;
            }

            // cria QR
            qrInstance = new QRCode(box, {
                text: url,
                width: 180,
                height: 180,
                correctLevel: QRCode.CorrectLevel.M
            });
        });

        $('#modalQrChave').on('hidden.bs.modal', function() {
            // limpa para não duplicar
            const box = document.getElementById('qrContainer');
            box.innerHTML = '';
            qrInstance = null;
        });

    });
</script>

<!-- (Sem alterações) – submissão AJAX do formulário -->
<script>
    jQuery(function($) {
        $('#formEditarTurma').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('cursosv1.0/ajax_TurmaUpdate.php', formData, function(response) {
                if (response.status === 'ok') {
                    alert(response.mensagem);
                } else {
                    alert('Erro: ' + (response.mensagem || 'Falha ao salvar.'));
                }
            }, 'json');
        });
    });
</script>

<!-- (Sem alterações) – finalizar/reativar turma -->
<script>
    (function() {
        const btn = document.getElementById('btnFinalizarTurma');
        const resp = document.getElementById('respFinalizarTurma');
        if (!btn) return;

        btn.addEventListener('click', function() {
            const acao = btn.dataset.acao;
            const msg = acao === 'finalizar' ?
                'Deseja realmente FINALIZAR esta turma?' :
                'Deseja realmente REATIVAR esta turma?';
            if (!confirm(msg)) return;

            btn.disabled = true;
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processando…';
            resp.textContent = '';

            fetch('cursosv1.0/ajax_FinalizarTurma.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        chave: '<?= $ChaveTurma ?>',
                        acao
                    })
                })
                .then(r => r.json())
                .then(j => {
                    if (j.status === 'ok') {
                        resp.innerHTML = '<span class="text-success">' + j.msg + '</span>';
                        if (acao === 'finalizar') {
                            btn.dataset.acao = 'reativar';
                            btn.className = 'btn btn-secondary btn-sm ms-2';
                            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Reativar turma';
                        } else {
                            btn.dataset.acao = 'finalizar';
                            btn.className = 'btn btn-danger btn-sm ms-2';
                            btn.innerHTML = '<i class="bi bi-flag-fill me-1"></i> Finalizar turma';
                        }
                    } else {
                        resp.innerHTML = '<span class="text-danger">' + (j.msg || 'Erro inesperado.') + '</span>';
                        btn.innerHTML = oldHtml;
                    }
                })
                .catch(() => {
                    resp.innerHTML = '<span class="text-danger">Falha de conexão.</span>';
                    btn.innerHTML = oldHtml;
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    })();
</script>