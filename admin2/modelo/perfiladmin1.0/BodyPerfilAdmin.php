<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// 1) Validar hash do ID e descriptografar
$hashId = $_GET['id'] ?? '';
if ($hashId === '') {
    echo "<div class='alert alert-warning'>ID de usuário inválido.</div>";
    return;
}

$idDec = encrypt($hashId, $action = 'd'); // descriptografa
if (!is_numeric($idDec) || (int)$idDec <= 0) {
    echo "<div class='alert alert-warning'>ID de usuário inválido.</div>";
    return;
}
$idUsuario = (int)$idDec;

// 2) Garantir conexão PDO ($con)
try {
    if (!isset($con) || !($con instanceof PDO)) {
        $con = config::connect();
    }
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // 3) Consulta segura
    $stmt = $con->prepare("
        SELECT 
            codigousuario,
            nome,
            email,
            celular,
            dataaniversario,
            senha,
            chave,
            pastasu,
            imagem,
            imagem50,
            imagem200,
            size,
            nivel,
            liberado,
            onlinesu,
            timestampsu
        FROM new_sistema_usuario
        WHERE codigousuario = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo "<div class='alert alert-danger'>Usuário não encontrado.</div>";
        return;
    }

    $rw = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4) Foto (usa imagem200 se existir, senão imagem)
    $imgExibe = $rw['imagem200'] ?: ($rw['imagem'] ?: 'usuario.jpg');

    // Montagem do caminho (ajuste conforme sua função utilitária de fotos)
    if ($imgExibe === 'usuario.jpg') {
        $fotoAtual = '../../fotos/usuarios/usuario.png';
    } else {
        $pasta = $rw['pastasu'] ?? '';
        $fotoAtual = "../../fotos/usuarios/" . htmlspecialchars($pasta) . "/" . htmlspecialchars($imgExibe);
    }

    // 5) Map de níveis
    $niveis = [
        1 => 'Admin',
        2 => 'Suporte',
        3 => 'Professor',
        4 => 'Vendas'
    ];
} catch (Throwable $e) {
    // Dica: para depurar, temporariamente exiba a mensagem abaixo:
    // echo "<pre>{$e->getMessage()}</pre>";
    echo "<div class='alert alert-danger'>Erro ao carregar usuário.</div>";
    return;
}

?>
<?php

$dec = encrypt($rw['senha'], $action = 'd');

?>
<div class="container-fluid" data-aos="fade-up">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="text-white mb-0"><i class="bi bi-person-gear me-2"></i> Perfil do Administrador</h5>
        <?php if ($codadm == '1'): ?>
            <small class="text-muted">Código: <?= $dec; ?></small>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Coluna Foto -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="card-title mb-3"><i class="bi bi-image me-2"></i>Foto de Perfil</h6>
                    <?php echo $dec = encrypt("encyYmhKcmVHbWVhcTB4ZS9GKzhuUlZRU3VIQ3RXWXV4eFkySGx5VE0xVVlUdjhWUlcxV2ZaTTlKVU8xOWVvZA==", $action = 'd');;  ?>
                    <div class="text-center mb-3">
                        <img id="previewFoto"
                            src="<?= htmlspecialchars($fotoAtual) ?>"
                            alt="Foto do usuário"
                            class="rounded-circle border"
                            style="width: 160px; height: 160px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Enviar nova foto (JPG/PNG até 5MB)</label>
                        <input type="file" class="form-control" id="imagemPerfil" name="imagemPerfil" accept=".jpg,.jpeg,.png">
                        <div class="form-text">A foto será redimensionada para gerar a <code>imagem200</code>.</div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnRemoverFoto">
                        <i class="bi bi-x-circle me-1"></i> Remover foto (usar padrão)
                    </button>
                </div>
            </div>
        </div>

        <!-- Coluna Form -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form id="formPerfilAdmin" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="codigousuario" value="<?= (int)$rw['codigousuario'] ?>">
                        <!-- CSRF simples (opcional): gere na página principal e injete aqui -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($rw['nome'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">E-mail</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($rw['email'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Celular</label>
                                <input type="text" class="form-control" name="celular" value="<?= htmlspecialchars($rw['celular'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data de Aniversário</label>
                                <input type="date" class="form-control" name="dataaniversario" value="<?= htmlspecialchars($rw['dataaniversario'] ?? '') ?>">
                            </div>

                            <?php if (temPermissao($niveladm, [1])): ?>
                                <div class="col-md-4">
                                    <label class="form-label">Nível</label>
                                    <select class="form-select" name="nivel" required>
                                        <?php foreach ($niveis as $valor => $rotulo): ?>
                                            <option value="<?= $valor ?>" <?= ((int)$rw['nivel'] === $valor ? 'selected' : '') ?>>
                                                <?= $rotulo ?> = <?= $valor ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="nivel" value="<?= (int)$rw['nivel'] ?>">
                            <?php endif; ?>

                            <div class="col-md-6">
                                <label class="form-label">Nova Senha (opcional)</label>
                                <input type="password" class="form-control" name="senha" placeholder="Deixe em branco para manter a atual">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Liberado</label>
                                <select class="form-select" name="liberado">
                                    <option value="1" <?= ((int)$rw['liberado'] === 1 ? 'selected' : '') ?>>Sim</option>
                                    <option value="0" <?= ((int)$rw['liberado'] === 0 ? 'selected' : '') ?>>Não</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Chave (somente leitura)</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($rw['chave'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Pasta Usuário (somente leitura)</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($rw['pastasu'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Salvar Alterações
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Campo real do arquivo (para envio via JS) -->
                        <input type="hidden" name="remover_foto" id="remover_foto" value="0">
                    </form>
                </div>
            </div>

            <small class="text-muted d-block mt-2">Última atualização: <?= htmlspecialchars($rw['timestampsu'] ?? '') ?></small>
        </div>
    </div>
</div>

<!-- Toast central -->
<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 1080;">
    <div id="toastPerfilAdmin" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body" id="toastPerfilAdminMsg">Salvo com sucesso.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formPerfilAdmin');
        const inputImg = document.getElementById('imagemPerfil');
        const preview = document.getElementById('previewFoto');
        const btnRemover = document.getElementById('btnRemoverFoto');
        const removerFoto = document.getElementById('remover_foto');
        const toastEl = document.getElementById('toastPerfilAdmin');
        const toastMsg = document.getElementById('toastPerfilAdminMsg');
        const bsToast = () => new bootstrap.Toast(toastEl);

        // Prévia da imagem
        inputImg?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                e.target.value = '';
                showToast('Formato inválido. Envie JPG ou PNG.');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                e.target.value = '';
                showToast('Arquivo muito grande. Máximo 5MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = () => preview.src = reader.result;
            reader.readAsDataURL(file);
            removerFoto.value = '0';
        });

        // Remover foto => usa padrão
        btnRemover?.addEventListener('click', () => {
            removerFoto.value = '1';
            preview.src = '../../fotos/usuarios/usuario.png';
            if (inputImg) inputImg.value = '';
            showToast('A foto será redefinida ao salvar.');
        });

        // Submit via AJAX
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);

            // Se houver arquivo selecionado, anexa
            if (inputImg?.files?.length) {
                fd.append('imagemPerfil', inputImg.files[0]);
            }

            try {
                const resp = await fetch('perfiladmin1.0/ajax_PerfilAdminUpdate.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();
                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Dados atualizados com sucesso.');
                } else {
                    showToast(json.mensagem || 'Falha ao salvar.', true);
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