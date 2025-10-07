<?php
// --- MÓDULO: Lista do Questionário da Publicação (modulado) ---
// Requisitos: $idPublicacao definido na página pai.

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$erro = null;
$idPub = null;
$rows = [];

try {
    if (!isset($idPublicacao)) {
        throw new RuntimeException('ID da publicação não informado.');
    }
    $idPub = (int)$idPublicacao;
    if ($idPub <= 0) {
        throw new RuntimeException('ID da publicação inválido.');
    }

    $sql = "SELECT 
                codigoquestionario,
                idpublicacaocq,
                titulocq,
                ordemcq,
                visivelcq
            FROM a_curso_questionario
            WHERE idpublicacaocq = :idpub
            ORDER BY ordemcq ASC, codigoquestionario ASC";

    $stmt = config::connect()->prepare($sql);
    $stmt->bindValue(':idpub', $idPub, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $th) {
    $erro = $th->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-ui-checks-grid me-2"></i> Questionário da Publicação
        <small class="text-muted ms-2">#<?= h($idPub ?: 0) ?></small>
    </h5>

    <a href="cursos_publicacaoQuestionarioNovo.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>"
        class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Adicionar novas perguntas
    </a>
</div>

<?php if ($erro): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= h($erro) ?></div>
    <?php return; ?>
<?php endif; ?>

<?php if (empty($rows)): ?>
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        Nenhuma pergunta cadastrada para esta publicação.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:90px">Ordem</th>
                    <th>Pergunta</th>
                    <th style="width:130px" class="text-center">Visível</th>
                    <th style="width:120px" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $cod   = (int)$r['codigoquestionario'];
                    $vis   = (int)$r['visivelcq'] === 1;
                    $icone = $vis ? 'bi-eye' : 'bi-eye-slash';
                    $badge = $vis ? 'bg-success' : 'bg-secondary';
                    $txt   = $vis ? 'Visível' : 'Oculto';
                ?>
                    <tr id="row-<?= $cod ?>">
                        <td>
                            <span class="badge bg-dark-subtle text-dark-emphasis"><?= h($r['ordemcq']) ?></span>
                        </td>
                        <td>
                            <a href="cursos_publicacaoQuestionarioView.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>&idQuest=<?= $cod ?>" class="link-primary text-decoration-none fw-semibold">
                                <?= h($r['titulocq']) ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <span id="badge-vis-<?= $cod ?>" class="badge <?= $badge ?>"><?= $txt ?></span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary"
                                    title="<?= $vis ? 'Tornar oculto' : 'Tornar visível' ?>"
                                    onclick="toggleVisivel(<?= $cod ?>, <?= $idPub ?>, this)">
                                    <i id="ico-vis-<?= $cod ?>" class="bi <?= $icone ?>"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    title="Excluir pergunta"
                                    onclick="excluirPergunta(<?= $cod ?>, <?= $idPub ?>, this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    // Utilitário para "spinner" no próprio botão (sem overlay)
    function withBtnLoading(btn, fn) {
        if (!btn) return fn();
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        const done = () => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        };
        Promise.resolve(fn()).finally(done);
    }

    // Alternar visibilidade
    function toggleVisivel(codigo, idpub, btn) {
        withBtnLoading(btn, () =>
            fetch('questionariov1.0/ajax_toggleVisivel.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: (() => {
                    const fd = new FormData();
                    fd.append('codigo', codigo);
                    fd.append('idpublicacao', idpub);
                    return fd;
                })()
            })
            .then(r => r.json())
            .then(j => {
                if (!j || !j.success) throw new Error(j?.message || 'Erro ao alternar visibilidade.');
                const icon = document.getElementById('ico-vis-' + codigo);
                const badge = document.getElementById('badge-vis-' + codigo);
                if (icon) icon.className = 'bi ' + (j.visivel === 1 ? 'bi-eye' : 'bi-eye-slash');
                if (badge) {
                    badge.className = 'badge ' + (j.visivel === 1 ? 'bg-success' : 'bg-secondary');
                    badge.textContent = j.visivel === 1 ? 'Visível' : 'Oculto';
                }
            })
            .catch(err => alert(err.message || 'Falha ao alternar visibilidade.'))
        );
    }

    // Excluir pergunta
    function excluirPergunta(codigo, idpub, btn) {
        if (!confirm('Confirma excluir esta pergunta do questionário?')) return;
        withBtnLoading(btn, () =>
            fetch('questionariov1.0/ajax_deletePergunta.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: (() => {
                    const fd = new FormData();
                    fd.append('codigo', codigo);
                    fd.append('idpublicacao', idpub);
                    return fd;
                })()
            })
            .then(r => r.json())
            .then(j => {
                if (!j || !j.success) throw new Error(j?.message || 'Erro ao excluir pergunta.');
                const row = document.getElementById('row-' + codigo);
                if (row && row.parentNode) row.parentNode.removeChild(row);
            })
            .catch(err => alert(err.message || 'Falha ao excluir pergunta.'))
        );
    }
</script>