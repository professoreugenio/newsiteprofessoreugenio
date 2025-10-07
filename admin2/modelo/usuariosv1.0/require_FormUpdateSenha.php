<!-- ===== Alterar Senha (card aprimorado) ===== -->
<style>
    .card-elegant {
        border: 0 !important;
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .card-head-soft {
        background: linear-gradient(135deg, #112240 0%, #0f1c36 60%);
        color: #fff;
        border-radius: 1rem 1rem 0 0;
        padding: 14px 18px;
    }

    .hint {
        font-size: .85rem;
        color: #6c757d;
    }

    .strength-wrap {
        height: 8px;
        background: #e9ecef;
        border-radius: 999px;
        overflow: hidden;
    }

    .strength-bar {
        width: 0%;
        height: 100%;
        transition: width .25s ease;
    }

    .strength-1 {
        background: #dc3545;
    }

    /* fraca */
    .strength-2 {
        background: #fd7e14;
    }

    /* média- */
    .strength-3 {
        background: #ffc107;
    }

    /* média */
    .strength-4 {
        background: #198754;
    }

    /* forte */
</style>

<div class="card card-elegant mt-4">
    <div class="card-head-soft d-flex align-items-center gap-2">
        <i class="bi bi-shield-lock-fill fs-5"></i>
        <div class="fw-semibold">Alterar Senha</div>
    </div>

    <div class="card-body p-4">
        <form id="formSenha" method="post" class="row g-3">
            <input type="hidden" name="idUsuario" value="<?= (int)$codigoCadastro ?>">

            <!-- Nova senha -->
            <div class="col-md-6">
                <label class="form-label">Nova senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" name="novaSenha" id="novaSenha" class="form-control" placeholder="Digite a nova senha" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleNova"><i class="bi bi-eye"></i></button>
                </div>
                <div class="mt-2 strength-wrap" aria-hidden="true">
                    <div id="strengthBar" class="strength-bar"></div>
                </div>
                <div id="strengthText" class="hint mt-1">Use ao menos 8 caracteres com letras e números.</div>
            </div>

            <!-- Confirmar senha -->
            <div class="col-md-6">
                <label class="form-label">Confirmar senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-check2-square"></i></span>
                    <input type="password" name="confirmaSenha" id="confirmaSenha" class="form-control" placeholder="Repita a nova senha" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirma"><i class="bi bi-eye"></i></button>
                </div>
                <div id="matchHint" class="hint mt-1"></div>
            </div>

            <div class="col-12 d-grid d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary px-4" id="btnSalvarSenha">
                    <i class="bi bi-key-fill me-1"></i> Alterar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Mostrar/ocultar
    (function() {
        const nova = document.getElementById('novaSenha');
        const conf = document.getElementById('confirmaSenha');
        const tNova = document.getElementById('toggleNova');
        const tConf = document.getElementById('toggleConfirma');
        const bar = document.getElementById('strengthBar');
        const txt = document.getElementById('strengthText');
        const match = document.getElementById('matchHint');

        const toggle = (input, btn) => {
            const eye = btn.querySelector('i');
            input.type = input.type === 'password' ? 'text' : 'password';
            eye.classList.toggle('bi-eye');
            eye.classList.toggle('bi-eye-slash');
        };
        tNova.addEventListener('click', () => toggle(nova, tNova));
        tConf.addEventListener('click', () => toggle(conf, tConf));

        // Medidor de força simples
        const score = s => {
            let c = 0;
            if (!s) return 0;
            if (s.length >= 8) c++;
            if (/[A-Z]/.test(s)) c++;
            if (/[0-9]/.test(s)) c++;
            if (/[^A-Za-z0-9]/.test(s)) c++;
            return c; // 0-4
        };
        const renderStrength = v => {
            const pct = [0, 25, 50, 75, 100][v];
            bar.className = 'strength-bar strength-' + (v || 1);
            bar.style.width = pct + '%';
            const labels = ['Muito fraca', 'Fraca', 'Média', 'Boa', 'Forte'];
            const colors = ['#dc3545', '#dc3545', '#ffc107', '#198754', '#198754'];
            txt.textContent = labels[v] || 'Muito fraca';
            txt.style.color = colors[v] || '#6c757d';
        };

        nova.addEventListener('input', () => {
            renderStrength(score(nova.value));
            // checagem de match ao digitar
            if (conf.value.length) {
                if (nova.value === conf.value) {
                    match.textContent = 'Senhas conferem.';
                    match.style.color = '#198754';
                } else {
                    match.textContent = 'As senhas não conferem.';
                    match.style.color = '#dc3545';
                }
            } else {
                match.textContent = '';
            }
        });

        conf.addEventListener('input', () => {
            if (nova.value === conf.value) {
                match.textContent = 'Senhas conferem.';
                match.style.color = '#198754';
            } else {
                match.textContent = 'As senhas não conferem.';
                match.style.color = '#dc3545';
            }
        });

        // AJAX (mantendo seu endpoint e limpeza após sucesso)
        $(function() {
            $('#formSenha').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btnSalvarSenha');
                $btn.prop('disabled', true).addClass('disabled');

                $.ajax({
                        url: 'usuariosv1.0/ajax_update_senha_aluno.php',
                        method: 'POST',
                        data: $form.serialize(),
                        dataType: 'json'
                    })
                    .done(function(res) {
                        if (res.ok) {
                            // toast/alert de sucesso
                            alert(res.msg || 'Senha atualizada com sucesso!');
                            // limpar campos + UI
                            $('#novaSenha, #confirmaSenha').val('');
                            renderStrength(0);
                            match.textContent = '';
                        } else {
                            alert(res.msg || 'Erro ao atualizar a senha.');
                        }
                    })
                    .fail(function() {
                        alert('Falha de comunicação com o servidor.');
                    })
                    .always(function() {
                        $btn.prop('disabled', false).removeClass('disabled');
                    });
            });
        });
    })();
</script>