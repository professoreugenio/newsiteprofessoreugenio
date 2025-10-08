<div class="col-md-12 mt-4 mb-4">
    <label class="form-label fw-semibold">Chave da Turma</label>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-warning" id="btnGerarChave">
            <i class="bi bi-key me-1"></i> Gerar Chave
        </button>

        <div class="input-group" style="max-width: 250px;">
            <input type="text" id="campoChave" class="form-control" readonly value="<?= htmlspecialchars($rwChave['chavesc'] ?? '••••••••') ?>" style="letter-spacing: 3px;" data-real-value="<?= htmlspecialchars($rwChave['chavesc'] ?? '') ?>">
            <button class="btn btn-outline-secondary" type="button" id="btnToggleChave">
                <i class="bi bi-eye" id="iconeOlho"></i>
            </button>
        </div>

        <?php if ($andamento == '1'): ?>
            <button type="button" id="btnFinalizarTurma" class="btn btn-secondary btn-sm ms-2"
                data-acao="reativar">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reativar turma
            </button>
        <?php else: ?>
            <button type="button" id="btnFinalizarTurma" class="btn btn-danger btn-sm ms-2"
                data-acao="finalizar">
                <i class="bi bi-flag-fill me-1"></i> Finalizar turma
            </button>
        <?php endif; ?>

        <span id="respFinalizarTurma" class="ms-2 small"></span>

    </div>
</div>



<form id="formEditarTurma" class="row g-4" method="post" data-aos="fade-up">

    <!-- CHECKBOXES -->
    <div class="col-md-12 mt-4">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="visivelst" name="visivelst" value="1" <?= $chkon ?>>
            <label class="form-check-label" for="visivelst">Visível no site</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="andamento" name="andamento" value="1" <?= $chkanda ?>>
            <label class="form-check-label" for="andamento">Em andamento</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="comercialt" name="comercialt" value="1" <?= $chcom ?>>
            <label class="form-check-label" for="comercialt">Comercial</label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="visiveltube" name="visiveltube" value="1" <?= $chkytube ?>>
            <label class="form-check-label" for="visiveltube">Visível no YouTube</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="institucional" name="institucional" value="1" <?= $institucional ?>>
            <label class="form-check-label" for="visiveltube">Institucional</label>
        </div>
    </div>
    <input type="hidden" name="chave" value="<?= htmlspecialchars($ChaveTurma) ?>">

    <!-- SELECT PRODUTO AFILIADO -->
    <div class="col-md-4">
        <?php echo $produtoafiliado;  ?>
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
                    echo '<option value="' . $row['codigoprodutoafiliado'] . '" ' . $selected . '>' . htmlspecialchars($row['nomeap']) ." ". $row['codigoprodutoafiliado']. '</option>';
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

    <!-- Horários -->
    <div class="col-12">
        <h5 class="mt-3">⏰ Horários das Turmas</h5>
        <div class="row g-3">
            <!-- Manhã -->
            <div class="col-md-2">
                <label class="form-label">Manhã (De)</label>
                <input type="time" class="form-control" name="manha_de" value="<?= $horadem ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Manhã (Às)</label>
                <input type="time" class="form-control" name="manha_as" value="<?= $horaparam ?>">
            </div>

            <!-- Tarde -->
            <div class="col-md-2">
                <label class="form-label">Tarde (De)</label>
                <input type="time" class="form-control" name="tarde_de" value="<?= $horadet ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tarde (Às)</label>
                <input type="time" class="form-control" name="tarde_as" value="<?= $horaparat ?>">
            </div>

            <!-- Noite -->
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

    <div class="col-md-5">
        <label for="linkWhatsapp" class="form-label">Link do WhatsApp</label>
        <input type="url" class="form-control" id="linkWhatsapp" name="linkwhatsapp" value="<?= htmlspecialchars($linkWhatsapp) ?>">
    </div>

    <div class="col-md-5">
        <label for="linkYoutube" class="form-label">Link do YouTube</label>
        <input type="url" class="form-control" id="linkYoutube" name="youtubesct" value="<?= htmlspecialchars($linkYoutube) ?>">
    </div>

    <div class="col-md-12">
        <label for="detalhes" class="form-label">Prévia</label>
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



    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success me-2">
            <i class="bi bi-save me-1"></i>Salvar Alterações
        </button>
        <a href="cursos_turmas.php?id=<?= $_GET['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</form>

<script>
    jQuery(function($) {
        const chaveturma = "<?= $ChaveTurma ?>";

        $('#btnGerarChave').click(function() {
            $.post('cursosv1.0/ajax_chaveTurma.php', {
                chaveturma: chaveturma
            }, function(res) {
                try {
                    const data = JSON.parse(res);
                    if (data.sucesso) {
                        $('#campoChave').val(data.chave).data('real-value', data.chave);
                        $('#iconeOlho').removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                } catch (e) {
                    console.error('Erro ao gerar chave:', e);
                    alert('Erro ao gerar chave.');
                }
            });
        });

        $('#btnToggleChave').click(function() {
            const campo = $('#campoChave');
            const realVal = campo.data('real-value') || '';
            const isHidden = campo.val().includes('•');

            campo.val(isHidden ? realVal : '••••••••');
            $('#iconeOlho').toggleClass('bi-eye bi-eye-slash');
        });
    });
</script>

<script>
    jQuery(function($) {
        $('#formEditarTurma').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.post('cursosv1.0/ajax_TurmaUpdate.php', formData, function(response) {
                if (response.status === 'ok') {
                    alert(response.mensagem); // Substitua por toast se desejar
                } else {
                    alert('Erro: ' + response.mensagem);
                }
            }, 'json');
        });
    });
</script>


<script>
    (function() {
        const btn = document.getElementById('btnFinalizarTurma');
        const resp = document.getElementById('respFinalizarTurma');
        if (!btn) return;

        btn.addEventListener('click', function() {
            const acao = btn.dataset.acao; // "finalizar" ou "reativar"
            const msgConfirm = acao === 'finalizar' ?
                'Deseja realmente FINALIZAR esta turma?' :
                'Deseja realmente REATIVAR esta turma?';

            if (!confirm(msgConfirm)) return;

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
                        acao: acao
                    })
                })
                .then(r => r.json())
                .then(j => {
                    if (j.status === 'ok') {
                        resp.innerHTML = '<span class="text-success">' + j.msg + '</span>';
                        // Alterna o botão no front
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