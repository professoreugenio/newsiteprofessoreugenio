<?php

/** ===== Helpers ===== */
function nomePrimeiroESegundo(string $nome): string
{
    $nome = trim(preg_replace('/\s+/', ' ', $nome));
    if ($nome === '') return '';
    $p = explode(' ', $nome);
    return trim(($p[0] ?? '') . ' ' . ($p[1] ?? ''));
}

function dtBR(?string $iso): string
{
    if (!$iso || $iso === '0000-00-00') return '-';
    $d = DateTime::createFromFormat('Y-m-d', $iso);
    return $d ? $d->format('d/m/Y') : '-';
}

function enc($v)
{
    return encrypt((string)$v, 'e');
}

/**
 * Consulta: 4 últimas inscrições
 */
$sql = "
    SELECT 
        i.codigoinscricao,
        i.codigousuario,
        i.data_ins,
        i.hora_ins,
        i.dataprazosi,
        i.chaveturma,

        c.codigocadastro,
        c.nome AS nome_aluno,

        t.codigoturma,
        t.codcursost,
        t.nometurma,
        t.comercialt,
        t.institucional
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c 
        ON c.codigocadastro = i.codigousuario
    INNER JOIN new_sistema_cursos_turmas t 
        ON t.chave = i.chaveturma
    WHERE i.data_ins IS NOT NULL
    ORDER BY i.data_ins DESC, i.hora_ins DESC
    LIMIT 4
";
$stmt = $con->prepare($sql);
$stmt->execute();
$ultimas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de inscrições de hoje
$hoje = date('Y-m-d');
$stmtHoje = $con->prepare("SELECT COUNT(*) FROM new_sistema_inscricao_PJA WHERE data_ins = :hoje");
$stmtHoje->bindValue(':hoje', $hoje);
$stmtHoje->execute();
$totalHoje = (int)$stmtHoje->fetchColumn();
?>

<style>
    /* Cards compactos e modernos */
    .insc-grid {
        display: grid;
        gap: .9rem;
        grid-template-columns: repeat(4, 1fr);
    }

    @media (max-width: 1200px) {
        .insc-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .insc-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .insc-grid {
            grid-template-columns: 1fr;
        }
    }

    .card-insc {
        position: relative;
        border: 1px solid rgba(17, 34, 64, .12);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .card-insc::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0, 187, 156, .12), rgba(255, 156, 0, .12));
        z-index: 0;
    }

    .card-insc>.inner {
        position: relative;
        z-index: 1;
        padding: .9rem .95rem;
        background: #fff;
    }

    .card-insc:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .badge-mini {
        font-size: .72rem;
        padding: .25rem .5rem;
        border-radius: 999px;
        letter-spacing: .2px;
    }

    .badge-comercial {
        background: #00BB9C;
        color: #fff;
    }

    .badge-instit {
        background: #112240;
        color: #fff;
    }

    .badge-prazo {
        background: #FF9C00;
        color: #112240;
    }

    .insc-title {
        font-weight: 700;
        font-size: .98rem;
        margin: 0;
        color: #0e1633;
    }

    .insc-title a {
        text-decoration: none;
        color: inherit;
    }

    .insc-title a:hover {
        text-decoration: underline;
    }

    .insc-sub {
        margin: .15rem 0 0 0;
        font-size: .84rem;
        color: #4b5563;
    }

    .insc-sub a {
        color: #112240;
        text-decoration: none;
        font-weight: 600;
    }

    .insc-sub a:hover {
        text-decoration: underline;
    }

    .insc-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-top: .55rem;
    }

    .insc-date {
        font-size: .82rem;
        color: #334155;
        font-weight: 600;
    }

    .insc-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #00BB9C;
        display: inline-block;
        margin-right: .4rem;
    }
</style>

<!-- BOTÃO DE ABRIR MODAL -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Painel</h5>
    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInscricoes">
        📋 Últimas Inscrições (<?= $totalHoje; ?> hoje)
    </button>
</div>

<!-- MODAL DE INSCRIÇÕES -->
<div class="modal fade" id="modalInscricoes" tabindex="-1" aria-labelledby="modalInscricoesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalInscricoesLabel">📋 Últimas Inscrições</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex align-items-end justify-content-between mb-2">
                    <small class="text-muted">Atualizado <?= date('d/m/Y H:i'); ?></small>
                </div>

                <?php if (empty($ultimas)): ?>
                    <div class="alert alert-light border">Nenhuma inscrição encontrada.</div>
                <?php else: ?>
                    <div class="insc-grid">
                        <?php foreach ($ultimas as $r):
                            $nome2  = nomePrimeiroESegundo($r['nome_aluno'] ?? '');
                            $dtIns  = dtBR($r['data_ins'] ?? null);
                            $prazo  = dtBR($r['dataprazosi'] ?? null);
                            $isCom  = (int)($r['comercialt'] ?? 0) === 1;
                            $isInst = (int)($r['institucional'] ?? 0) === 1;

                            $encUser  = enc($r['codigocadastro'] ?? '');
                            $encCurso = enc($r['codcursost'] ?? '');
                            $encTurma = enc($r['codigoturma'] ?? '');

                            $linkAluno = 'alunoTurmas.php?idUsuario=' . rawurlencode($encUser);
                            $linkTurma = 'cursos_TurmasAlunos.php?id=' . rawurlencode($encCurso) . '&tm=' . rawurlencode($encTurma);
                        ?>
                            <div class="card-insc" title="Inscrição #<?= (int)$r['codigoinscricao']; ?>">
                                <div class="inner">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <p class="insc-title mb-0">
                                            <span class="insc-dot"></span>
                                            <a href="<?= $linkAluno; ?>"><?= htmlspecialchars($nome2); ?></a>
                                        </p>
                                        <div class="d-flex gap-1">
                                            <?php if ($isCom): ?>
                                                <span class="badge-mini badge-comercial" title="Turma Comercial">Comercial</span>
                                            <?php endif; ?>
                                            <?php if ($isInst): ?>
                                                <span class="badge-mini badge-instit" title="Turma Institucional">Institucional</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="insc-sub">
                                        Turma: <a href="<?= $linkTurma; ?>"><?= htmlspecialchars($r['nometurma'] ?? '-'); ?></a>
                                    </p>

                                    <div class="insc-row">
                                        <span class="insc-date">
                                            <i class="bi bi-calendar2-check"></i> Inscrito em <?= $dtIns; ?>
                                        </span>
                                        <?php if ($prazo !== '-'): ?>
                                            <span class="badge-mini badge-prazo"><?= $prazo; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>