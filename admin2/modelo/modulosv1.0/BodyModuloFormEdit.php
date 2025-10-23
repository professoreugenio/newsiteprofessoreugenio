<?php

/**
 * BodyModuloFormEditar.php
 * Requisitos: $con (PDO) disponível, Bootstrap 5+, Bootstrap Icons, jQuery (para a máscara).
 * Não incluir HTML/HEAD ou conexões aqui (padrão de módulos).
 */

// --- IDs vindos por GET (criptografados)
$idCurso  = encrypt($_GET['id'] ?? '', $action = 'd'); // curso
$idModulo = encrypt($_GET['md'] ?? '', $action = 'd'); // módulo

$idCurso  = (int)$idCurso;
$idModulo = (int)$idModulo;

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

if ($idModulo <= 0) {
    echo '<div class="alert alert-warning">Módulo não informado.</div>';
    return;
}

/* --------- Carregar cursos para o select --------- */
try {
    // Mantém filtros comerciais/visibilidade se existirem; caso não existam, a consulta ainda funciona.
    $sqlCursos = "
        SELECT 
            codigocursos  AS id,
            nomecurso     AS nome
        FROM new_sistema_cursos
        WHERE nomecurso IS NOT NULL 
          AND nomecurso <> ''
          /* campos abaixo podem não existir em alguns ambientes; remova se necessário */
          /* AND visivelsc = 1 AND comercialsc = 1 */
        ORDER BY nomecurso ASC
    ";
    $stmtCursos   = $con->query($sqlCursos);
    $listaCursos  = $stmtCursos ? $stmtCursos->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    $listaCursos = [];
}

/* --------- Carregar dados do módulo --------- */
try {
    $sql = "
        SELECT 
            codigomodulos,
            codcursos,
            nomemodulo,
            descricao,
            bgcolorsm,
            imagem,
            ordemm,
            visivelm,
            visivelhome
        FROM new_sistema_modulos_PJA
        WHERE codigomodulos = :id
        LIMIT 1
    ";
    $st = $con->prepare($sql);
    $st->bindValue(':id', $idModulo, PDO::PARAM_INT);
    $st->execute();
    $mod = $st->fetch(PDO::FETCH_ASSOC);

    if (!$mod) {
        echo '<div class="alert alert-warning">Módulo não encontrado.</div>';
        return;
    }

    // Caso não tenha vindo o id do curso por GET, usa o do módulo
    if ($idCurso <= 0) {
        $idCurso = (int)($mod['codcursos'] ?? 0);
    }
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Erro ao carregar módulo: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}

?>

<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white border-0">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-view-stacked fs-5 text-primary"></i>
                <div class="fw-semibold fs-5 m-0">Editar módulo</div>
            </div>
            <div>
                <?php if (defined('APP_ROOT')): ?>
                    <?php @require_once APP_ROOT . '/admin2/modelo/modulosv1.0/SubnavSecundarioModulo.php'; ?>
                <?php endif; ?>
            </div>
        </div>
        <small class="text-muted">Atualize os dados do módulo e salve. Campos com * são obrigatórios.</small>
    </div>

    <div class="card-body pt-2">
        <form id="formModuloEditar" class="row g-3">
            <!-- ID oculto do módulo -->
            <input type="hidden" name="codigomodulos" value="<?= (int)$mod['codigomodulos'] ?>">

            <!-- Curso -->
            <div class="col-md-5">
                <select class="form-select" id="codcursos" name="codcursos" required>
                    <option value="">— selecione Um Curso —</option>
                    <?php foreach ($listaCursos as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)($mod['codcursos'] ?? 0)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome'] ?? ('Curso ' . (int)$c['id'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nome do módulo -->
            <div class="col-md-5">
                <div class="form-floating">
                    <input type="text"
                        class="form-control"
                        id="nomemodulo"
                        name="nomemodulo"
                        placeholder="Nome do módulo"
                        value="<?= htmlspecialchars($mod['nomemodulo'] ?? '') ?>"
                        required>
                    <label for="nomemodulo">Nome do módulo *</label>
                </div>
            </div>

            <!-- Ordem -->
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="number"
                        class="form-control"
                        id="ordemm"
                        name="ordemm"
                        placeholder="Ordem"
                        min="0"
                        value="<?= (int)($mod['ordemm'] ?? 1) ?>">
                    <label for="ordemm">Ordem</label>
                </div>
            </div>

            <!-- Descrição -->
            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control"
                        id="descricao"
                        name="descricao"
                        placeholder="Descrição"
                        style="height: 110px"><?= htmlspecialchars($mod['descricao'] ?? '') ?></textarea>
                    <label for="descricao">Descrição</label>
                </div>
            </div>

            <!-- Cor de fundo e imagem -->
            <div class="col-md-3">
                <label for="bgcolorsm" class="form-label fw-semibold">Cor de fundo</label>
                <input type="color"
                    class="form-control form-control-color"
                    id="bgcolorsm"
                    name="bgcolorsm"
                    value="<?= htmlspecialchars($mod['bgcolorsm'] ?: '#00bb9c') ?>"
                    title="Escolha a cor">
            </div>

            <div class="col-md-9">
                <div class="form-floating">
                    <input type="text"
                        class="form-control"
                        id="imagem"
                        name="imagem"
                        placeholder="Nome do arquivo da imagem"
                        value="<?= htmlspecialchars($mod['imagem'] ?: 'padrao.jpg') ?>">
                    <label for="imagem">Imagem (arquivo ou caminho)</label>
                </div>
                <small class="text-muted">Padrão: <code>padrao.jpg</code>. (Upload pode ser tratado em endpoint dedicado.)</small>
            </div>



            <!-- Visibilidades -->
            <div class="col-md-3">
                <label class="form-label fw-semibold d-block">Visível?</label>
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="visivelm"
                        name="visivelm"
                        <?= ((string)($mod['visivelm'] ?? '0') === '1') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="visivelm">Exibir módulo</label>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold d-block">Visível na Home?</label>
                <div class="form-check form-switch">
                    <input class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="visivelhome"
                        name="visivelhome"
                        <?= ((int)($mod['visivelhome'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="visivelhome">Exibir na página inicial</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Salvar alterações
                </button>
                <a href="cursos_publicacoes.php?id=<?= urlencode($_GET['id'] ?? '') ?>&md=<?= urlencode($_GET['md'] ?? '') ?>"
                    class="btn btn-outline-secondary">
                    Voltar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Toast centralizado -->
<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 1080;">
    <div id="toastModulo" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toastModuloBody" class="toast-body">Processando...</div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formModuloEditar');
        const toastEl = document.getElementById('toastModulo');
        const toastBd = document.getElementById('toastModuloBody');
        const bsToast = (window.bootstrap && window.bootstrap.Toast) ? new bootstrap.Toast(toastEl) : null;

        function showToast(msg, ok) {
            if (toastBd) toastBd.textContent = msg;
            if (toastEl) {
                toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-secondary');
                toastEl.classList.add(ok ? 'text-bg-success' : 'text-bg-danger');
            }
            if (bsToast) bsToast.show();
            else alert(msg);
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const fd = new FormData(form);
            // Switch -> 0/1
            fd.set('visivelm', document.getElementById('visivelm').checked ? '1' : '0');
            fd.set('visivelhome', document.getElementById('visivelhome').checked ? '1' : '0');

            try {
                const resp = await fetch('modulosv1.0/ajax_moduloUpdate.php', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await resp.json();

                if (data && data.success) {
                    showToast(data.message || 'Módulo atualizado com sucesso!', true);
                } else {
                    showToast((data && data.message) ? data.message : 'Falha ao atualizar o módulo.', false);
                }
            } catch (err) {
                showToast('Erro de comunicação. ' + err, false);
            }
        });
    })();
</script>