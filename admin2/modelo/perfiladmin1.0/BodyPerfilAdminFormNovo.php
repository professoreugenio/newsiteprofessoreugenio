<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// $niveladm deve estar disponível na página (ex.: vindo do autenticacao.php)
if (!function_exists('temPermissao')) {
    function temPermissao($nivelUsuario, $permitidos = [])
    {
        return in_array($nivelUsuario, $permitidos);
    }
}

if (!isset($niveladm)) {
    $niveladm = 0;
}

if (!temPermissao((int)$niveladm, [1])): ?>
    <div class="alert alert-warning">
        <i class="bi bi-shield-lock me-1"></i> Você não tem permissão para cadastrar novos administradores.
    </div>
    <?php return; ?>
<?php endif; ?>

<?php
// Mapa de níveis
$niveis = [
    1 => 'Admin',
    2 => 'Suporte',
    3 => 'Professor',
    4 => 'Vendas'
];

// CSRF (se já existir token global, use-o; senão, crie simples)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>

<div class="container-fluid" data-aos="fade-up">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="text-white mb-0">
            <i class="bi bi-person-plus-fill me-2"></i> Cadastrar Novo Admin/Usuário
        </h5>
    </div>

    <div class="row g-4">
        <!-- Coluna Foto -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3"><i class="bi bi-image me-2"></i>Foto de Perfil (opcional)</h6>

                    <div class="text-center mb-3">
                        <img id="previewFotoNovo"
                            src="../../fotos/usuarios/usuario.png"
                            alt="Prévia da foto"
                            class="rounded-circle border"
                            style="width: 160px; height: 160px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Enviar foto (JPG/PNG até 5MB)</label>
                        <input type="file" class="form-control" id="imagemPerfilNovo" name="imagemPerfil" accept=".jpg,.jpeg,.png">
                        <div class="form-text">Uma versão <code>imagem200</code> será gerada automaticamente.</div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary w-100" id="btnRemoverFotoNovo">
                        <i class="bi bi-x-circle me-1"></i> Remover foto (usar padrão)
                    </button>
                </div>
            </div>
        </div>

        <!-- Coluna Form -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="formPerfilAdminNovo" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="remover_foto" id="remover_foto_novo" value="0">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome *</label>
                                <input type="text" class="form-control" name="nome" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">E-mail *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control" name="celular">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data de Aniversário</label>
                                <input type="date" class="form-control" name="dataaniversario">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nível *</label>
                                <select class="form-select" name="nivel" required>
                                    <?php foreach ($niveis as $valor => $rotulo): ?>
                                        <option value="<?= $valor ?>"><?= $rotulo ?> = <?= $valor ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Apenas Admin (nível 1) pode escolher/alterar níveis.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Senha *</label>
                                <input type="password" class="form-control" name="senha" minlength="6" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirmar Senha *</label>
                                <input type="password" class="form-control" name="senha2" minlength="6" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Liberado</label>
                                <select class="form-select" name="liberado">
                                    <option value="1" selected>Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check2-circle me-1"></i> Cadastrar
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bi bi-eraser me-1"></i> Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <small class="text-muted d-block mt-2">Campos com * são obrigatórios.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast central -->
<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 1080;">
    <div id="toastPerfilAdminNovo" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
        <div class="d-flex">
            <div class="toast-body" id="toastPerfilAdminNovoMsg">Cadastro realizado.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formPerfilAdminNovo');
        const inputImg = document.getElementById('imagemPerfilNovo');
        const preview = document.getElementById('previewFotoNovo');
        const btnRemover = document.getElementById('btnRemoverFotoNovo');
        const removerFoto = document.getElementById('remover_foto_novo');
        const toastEl = document.getElementById('toastPerfilAdminNovo');
        const toastMsg = document.getElementById('toastPerfilAdminNovoMsg');
        const bsToast = () => new bootstrap.Toast(toastEl);

        inputImg?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                e.target.value = '';
                showToast('Formato inválido. Envie JPG ou PNG.', true);
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                e.target.value = '';
                showToast('Arquivo muito grande. Máximo 5MB.', true);
                return;
            }
            const reader = new FileReader();
            reader.onload = () => preview.src = reader.result;
            reader.readAsDataURL(file);
            removerFoto.value = '0';
        });

        btnRemover?.addEventListener('click', () => {
            removerFoto.value = '1';
            preview.src = '../../fotos/usuarios/usuario.png';
            if (inputImg) inputImg.value = '';
            showToast('A foto será definida como padrão.');
        });

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fd = new FormData(form);
            if (inputImg?.files?.length) {
                fd.append('imagemPerfil', inputImg.files[0]);
            }

            // Validação simples de senha
            const s1 = form.senha.value?.trim() ?? '';
            const s2 = form.senha2.value?.trim() ?? '';
            if (s1.length < 6 || s2.length < 6 || s1 !== s2) {
                showToast('Senha inválida ou não confere.', true);
                return;
            }

            try {
                const resp = await fetch('perfiladmin1.0/ajax_PerfilAdminInsert.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();
                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Cadastro realizado com sucesso.');
                    form.reset();
                    preview.src = '../../fotos/usuarios/usuario.png';
                    removerFoto.value = '0';
                    // Opcional: redirecionar para a edição do usuário recém-criado
                    // if (json.url) window.location.href = json.url;
                } else {
                    showToast(json.mensagem || 'Falha ao cadastrar.', true);
                }
            } catch (err) {
                showToast('Erro de comunicação com o servidor.', true);
            }
        });

        function showToast(msg, isError = false) {
            toastMsg.textContent = msg;
            toastEl.classList.remove('text-bg-dark', 'text-bg-danger', 'text-bg-success');
            toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
            bsToast().show();
        }
    })();
</script>