<?php
// Helper da foto (fallback)
function fotoAlunoUrl($pasta, $imagem)
{
    $url = "https://professoreugenio.com/fotos/usuarios/{$pasta}/{$imagem}";
    if (!$imagem) return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    $headers = @get_headers($url);
    return ($headers && strpos($headers[0], '200') !== false) ? $url : "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
}
$foto = fotoAlunoUrl($pastasc, $imagem200);
?>
<?php require 'usuariosv1.0/require_MsgsWhatsApp.php'; ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <img id="fotoAluno" src="<?= htmlspecialchars($foto) ?>" alt="Foto" class="rounded-circle me-3" style="width:88px;height:88px;object-fit:cover;border:2px solid #eee;">
            <div class="me-auto">
                <div class="fw-bold fs-5 mb-1"><?= htmlspecialchars($nome) ?></div>
                <div class="text-muted small">ID: <?= (int)$codigoCadastro ?> • Pasta: <?= htmlspecialchars($pastasc) ?></div>
                <div class="mt-2 d-flex align-items-center gap-2">
                    <input class="form-control form-control-sm" type="file" id="arquivoFoto" accept="image/*" style="max-width:290px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUploadFoto" disabled>
                        <i class="bi bi-upload"></i> Enviar nova foto
                    </button>
                    <div class="small text-muted">Formatos: JPG/PNG/WebP (até 5 MB)</div>
                </div>
                <input type="hidden" id="pastascAtual" value="<?= htmlspecialchars($pastasc) ?>">
            </div>

            <div>
                <button class="btn btn-outline-danger" id="btnExcluirAluno">
                    <i class="bi bi-trash3 me-1"></i> Excluir aluno
                </button>
            </div>
        </div>

        <!-- Form principal: dados do aluno -->

        <form id="formEditarAluno">
            <input type="hidden" name="idUsuario" value="<?= (int)$codigoCadastro ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($nome) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Data de Nascimento</label>
                    <input type="date" name="datanascimento_sc" class="form-control" value="<?= htmlspecialchars($datanascimento_sc) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail atual</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail anterior</label>
                    <input type="email" name="emailanterior" class="form-control" value="<?= htmlspecialchars($emailanterior) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Celular</label>
                    <input type="text" name="celular" class="form-control" value="<?= htmlspecialchars($celular) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($telefone) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estado (UF)</label>
                    <input type="text" name="estado" class="form-control" maxlength="2" value="<?= htmlspecialchars($estado) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Possui PC?</label>
                    <select name="possuipc" class="form-select">
                        <option value="" <?= ($possuipc === '' || $possuipc === null) ? 'selected' : ''; ?>>Não informado</option>
                        <option value="1" <?= ($possuipc == '1') ? 'selected' : ''; ?>>Sim</option>
                        <option value="0" <?= ($possuipc === '0') ? 'selected' : ''; ?>>Não</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bloqueio de E-mail</label>
                    <select name="emailbloqueio" class="form-select">
                        <option value="0" <?= ($emailbloqueio == '0' || $emailbloqueio === '') ? 'selected' : ''; ?>>Liberado</option>
                        <option value="1" <?= ($emailbloqueio == '1') ? 'selected' : ''; ?>>Bloqueado</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bloqueio de Post</label>
                    <select name="bloqueiopost" class="form-select">
                        <option value="0" <?= ($bloqueiopost == '0' || $bloqueiopost === '') ? 'selected' : ''; ?>>Não</option>
                        <option value="1" <?= ($bloqueiopost == '1') ? 'selected' : ''; ?>>Sim</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pasta (pastasc)</label>
                    <input type="text" name="pastasc" class="form-control" value="<?= htmlspecialchars($pastasc) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Imagem (imagem200)</label>
                    <input type="text" name="imagem200" id="imagem200" class="form-control" value="<?= htmlspecialchars($imagem200) ?>" readonly>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save2 me-1"></i> Salvar alterações
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <!-- Alteração de Senha (admin não precisa senha anterior) -->
        <div class="mb-2 fw-semibold">Alterar Senha </div>
        <form id="formSenha" class="row g-2">
            <input type="hidden" name="idUsuario" value="<?= (int)$codigoCadastro ?>">
            <div class="col-md-5">
                <input type="password" name="novaSenha" class="form-control" placeholder="Nova senha" required>
            </div>
            <div class="col-md-5">
                <input type="password" name="confirmaSenha" class="form-control" placeholder="Confirmar senha" required>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-key-fill me-1"></i> Alterar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast (top-center) -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1080;">
    <div id="toastMsg" class="toast text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">...</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>


<script src="usuariosv1.0/JS_updateUsuario.js"></script>