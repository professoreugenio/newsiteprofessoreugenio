<!-- ====== ESTILO RÁPIDO (cores do seu padrão) ====== -->


<div class="row g-4">
    <!-- Lateral: Perfil/Imagem -->
    <div class="col-lg-4 order-lg-2">

        <?php require 'usuariosv1.0/require_FormUploadFoto.php'; ?>
        <?php require 'usuariosv1.0/require_FormUpdateSenha.php'; ?>


    </div>

    <!-- Principal: Dados do Aluno -->
    <div class="col-lg-8 order-lg-1">
        <div class="card card-elegant">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="section-title h4 m-0"><i class="bi bi-person-lines-fill"></i> Dados do Aluno</div>
                    <span class="badge text-bg-light"><i class="bi bi-hash"></i> ID: <?= (int)$codigoCadastro ?></span>
                </div>
                <p class="help mb-4">Revise e atualize as informações do aluno. Campos com * são obrigatórios.</p>
                <?php echo $dec = encrypt($senha, $action = 'd'); ?>

                <!-- Form principal: dados do aluno -->
                <form id="formEditarAluno" novalidate>
                    <input type="hidden" name="idUsuario" value="<?= (int)$codigoCadastro ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-person"></i> Nome completo *</label>
                            <div class="position-relative input-icon">
                                <i class="bi bi-person"></i>
                                <input type="text" name="nome" class="form-control"
                                    value="<?= htmlspecialchars($nome) ?>" required>
                                <div class="invalid-feedback">Informe o nome completo.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-calendar-event"></i> Data de Nascimento</label>
                            <input type="date" name="datanascimento_sc" class="form-control"
                                value="<?= htmlspecialchars($datanascimento_sc) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-envelope-at"></i> E-mail atual</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($email) ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-envelope"></i> E-mail anterior</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-archive"></i></span>
                                <input type="email" name="emailanterior" class="form-control"
                                    value="<?= htmlspecialchars($emailanterior) ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-phone"></i> Celular</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" name="celular" id="celular" class="form-control"
                                    inputmode="tel" autocomplete="tel"
                                    value="<?= htmlspecialchars($celular) ?>">
                            </div>
                            <div class="help">Formato sugerido: (00) 00000-0000</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefone" id="telefone" class="form-control"
                                    inputmode="tel" autocomplete="tel"
                                    value="<?= htmlspecialchars($telefone) ?>">
                            </div>
                            <div class="help">Formato sugerido: (00) 0000-0000</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-geo"></i> Estado (UF)</label>
                            <input type="text" name="estado" id="estadoUF" class="form-control text-uppercase"
                                maxlength="2" value="<?= htmlspecialchars($estado) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-pc-display"></i> Possui PC?</label>
                            <select name="possuipc" class="form-select">
                                <option value="" <?= ($possuipc === '' || $possuipc === null) ? 'selected' : ''; ?>>Não informado</option>
                                <option value="1" <?= ($possuipc == '1') ? 'selected' : ''; ?>>Sim</option>
                                <option value="0" <?= ($possuipc === '0') ? 'selected' : ''; ?>>Não</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-envelope-exclamation"></i> Bloqueio de E-mail</label>
                            <select name="emailbloqueio" class="form-select">
                                <option value="0" <?= ($emailbloqueio == '0' || $emailbloqueio === '') ? 'selected' : ''; ?>>Liberado</option>
                                <option value="1" <?= ($emailbloqueio == '1') ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-chat-left-dots"></i> Bloqueio de Post</label>
                            <select name="bloqueiopost" class="form-select">
                                <option value="0" <?= ($bloqueiopost == '0' || $bloqueiopost === '') ? 'selected' : ''; ?>>Não</option>
                                <option value="1" <?= ($bloqueiopost == '1') ? 'selected' : ''; ?>>Sim</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-folder"></i> Pasta (pastasc)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-folder2-open"></i></span>
                                <input type="text" name="pastasc" class="form-control"
                                    value="<?= htmlspecialchars($pastasc) ?>">
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save2 me-1"></i> Salvar alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="small text-muted mt-3">
            <i class="bi bi-info-circle"></i> Dica: passe o mouse nos ícones para ver as dicas de cada campo.
        </div>
    </div>
</div>

<!-- ====== SCRIPTS DE UX (máscaras, UF maiúsculo, tooltips) ====== -->
<!-- jQuery (se já estiver no projeto, remova esta linha) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- jquery.mask (padrão preferido do Eugênio) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    (function() {
        // Tooltips Bootstrap (se tiver data-bs-title etc.)
        if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Máscaras BR
        $(function() {
            // Celular dinâmico (9 dígitos) e telefone fixo (8)
            $('#celular').mask('(00) 00000-0000');
            $('#telefone').mask('(00) 0000-0000');
        });

        // Força UF em maiúsculas
        const uf = document.getElementById('estadoUF');
        if (uf) {
            uf.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 2);
            });
        }

        // Validação HTML5 com feedback Bootstrap
        (function enableValidation() {
            const form = document.getElementById('formEditarAluno');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        })();
    })();
</script>


<script>
    $(function() {
        $('#formEditarAluno').on('submit', function(e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).addClass('disabled');

            $.ajax({
                    url: 'usuariosv1.0/ajax_update_aluno.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json'
                })
                .done(function(res) {
                    if (res.ok) {
                        // aqui você pode disparar um toast central no seu padrão
                        alert(res.msg);
                    } else {
                        let msg = res.msg || 'Falha ao atualizar.';
                        if (res.errors) {
                            msg += '\n' + Object.entries(res.errors).map(([k, v]) => `- ${k}: ${v}`).join('\n');
                        }
                        alert(msg);
                    }
                })
                .fail(function(xhr) {
                    alert('Erro de comunicação com o servidor.');
                })
                .always(function() {
                    $btn.prop('disabled', false).removeClass('disabled');
                });
        });
    });
</script>