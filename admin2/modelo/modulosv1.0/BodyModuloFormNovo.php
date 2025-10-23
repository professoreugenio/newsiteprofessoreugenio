<?php

/**
 * BodyModuloFormNovo.php
 * Requisitos: $con (PDO) disponível, Bootstrap 5+, Bootstrap Icons, jQuery (para a máscara).
 * Não incluir HTML/HEAD ou conexões aqui (segue seu padrão de módulos).
 */
$idCurso = $dec = encrypt($_GET['id'], $action = 'd');
if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

/* --------- Carregar cursos para o select --------- */
try {
    $sqlCursos = "
        SELECT 
            codigocursos  AS id,
            nomecurso AS nome
        FROM new_sistema_cursos
        WHERE nomecurso IS NOT NULL AND nomecurso <> '' AND visivelsc = 1 AND comercialsc = 1
        ORDER BY nomecurso ASC
    ";
    $stmtCursos = $con->query($sqlCursos);
    $listaCursos = $stmtCursos ? $stmtCursos->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    $listaCursos = [];
}

?>

<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-header bg-white border-0">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-view-stacked fs-5 text-primary"></i>
                <div class="fw-semibold fs-5 m-0">Cadastrar novo módulo</div>
            </div>
            <div><?php require_once APP_ROOT . '/admin2/modelo/modulosv1.0/SubnavSecundarioModulo.php'; ?></div>
        </div>

        <small class="text-muted">Preencha os dados do módulo e salve. Campos com * são obrigatórios.</small>
    </div>

    <div class="card-body pt-2">
        <form id="formModuloNovo" class="row g-3">
            <!-- Curso -->
            <div class="col-md-5">

                <select class="form-select" id="codcursos" name="codcursos" required>
                    <option value="">— selecione Um Curso —</option>
                    <?php foreach ($listaCursos as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (isset($idCurso) && (int)$c['id'] === (int)$idCurso) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome'] ?? ('Curso ' . (int)$c['id'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>

            <!-- Nome do módulo -->
            <div class="col-md-5">
                <div class="form-floating">
                    <input type="text" class="form-control" id="nomemodulosm" name="nomemodulosm" placeholder="Nome do módulo" required>
                    <label for="nomemodulosm">Nome do módulo *</label>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-floating">
                    <input type="number" class="form-control" id="ordemm" name="ordemm" placeholder="Ordem" min="0" value="1">
                    <label for="ordemm">Ordem</label>
                </div>
            </div>


            <!-- Descrição -->
            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control" id="descricao" name="descricao" placeholder="Descrição" style="height: 110px"></textarea>
                    <label for="descricao">Descrição</label>
                </div>
            </div>

            <!-- Cor de fundo e imagem -->
            <div class="col-md-3">
                <label for="bgcolorsm" class="form-label fw-semibold">Cor de fundo</label>
                <input type="color" class="form-control form-control-color" id="bgcolorsm" name="bgcolorsm" value="#00bb9c" title="Escolha a cor">
            </div>
            <div class="col-md-9">
                <div class="form-floating">
                    <input type="text" class="form-control" id="imagem" name="imagem" placeholder="Nome do arquivo da imagem" value="padrao.jpg">
                    <label for="imagem">Imagem (arquivo ou caminho)</label>
                </div>
                <small class="text-muted">Padrão: <code>padrao.jpg</code>. (Upload pode ser tratado em endpoint dedicado.)</small>
            </div>




            <!-- Carga horária e ordem -->


            <!-- Visibilidades -->
            <div class="col-md-3">
                <label class="form-label fw-semibold d-block">Visível?</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="visivelm" name="visivelm">
                    <label class="form-check-label" for="visivelm">Exibir módulo</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold d-block">Visível na Home?</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="visivelhome" name="visivelhome">
                    <label class="form-check-label" for="visivelhome">Exibir na página inicial</label>
                </div>
            </div>

            <!-- Data/Hora -->


            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Salvar módulo
                </button>
                <button type="reset" class="btn btn-outline-secondary">Limpar</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast centralizado -->
<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 1080;">
    <div id="toastModulo" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="toastModuloBody" class="toast-body">
                Processando...
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        // Carregar jquery.mask se necessário
        function ensureMask(callback) {
            if (typeof jQuery === 'undefined') {
                callback && callback();
                return;
            }
            if (typeof jQuery.fn.mask !== 'function') {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js';
                s.onload = callback;
                document.head.appendChild(s);
            } else {
                callback && callback();
            }
        }

        ensureMask(function() {
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.mask === 'function') {
                jQuery(function($) {
                    $('.money').mask('000.000.000,00', {
                        reverse: true
                    });
                });
            }
        });

        const form = document.getElementById('formModuloNovo');
        const toastEl = document.getElementById('toastModulo');
        const toastBody = document.getElementById('toastModuloBody');
        const bsToast = (window.bootstrap && window.bootstrap.Toast) ? new bootstrap.Toast(toastEl) : null;

        function showToast(msg, ok) {
            if (toastBody) toastBody.textContent = msg;
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
            fd.set('visivelm', (document.getElementById('visivelm').checked ? '1' : '0'));
            fd.set('visivelhome', (document.getElementById('visivelhome').checked ? '1' : '0'));

            try {
                const resp = await fetch('modulosv1.0/ajax_moduloInsertNovo.php', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await resp.json();

                if (data && data.success) {
                    showToast(data.message || 'Módulo criado com sucesso!', true);
                    form.reset();
                } else {
                    showToast((data && data.message) ? data.message : 'Falha ao criar o módulo.', false);
                }
            } catch (err) {
                showToast('Erro de comunicação. ' + err, false);
            }
        });
    })();
</script>