<?php
// ------------------------------------------------------
// alunoTurmas.php (módulo)
// Lista as turmas em que o aluno está inscrito e permite inscrevê-lo em novas turmas
// Requisitos externos: conexão já disponível via require da página principal
// Parâmetro esperado: GET idUsuario (CRIPTO) -> decrypt -> inteiro > 0
// ------------------------------------------------------

// 1) Validação do parâmetro
if (!isset($_GET['idUsuario']) || $_GET['idUsuario'] === '') {
    echo '<div class="alert alert-warning mb-0">Parâmetro "idUsuario" não informado.</div>';
    return;
}

$idUsuarioEnc = $_GET['idUsuario'];
$idUsuarioDec = encrypt($idUsuarioEnc, $action = 'd'); // decrypt
if (!is_numeric($idUsuarioDec)) {
    echo '<div class="alert alert-warning mb-0">ID do aluno inválido.</div>';
    return;
}
$idUsuario = (int)$idUsuarioDec;
if ($idUsuario <= 0) {
    echo '<div class="alert alert-warning mb-0">ID do aluno inválido.</div>';
    return;
}

try {
    $pdo = config::connect();

    // 2) Turmas já inscritas pelo aluno (para listar e também para marcar no seletor)
    $sql = "
        SELECT 
            i.dataprazosi         AS dataprazo,
            t.codcursost         AS idcurso,
            t.codigoturma        AS idturma,
            t.nometurma       AS titulo_turma,
            t.chave              AS chave_turma,
            i.data_ins           AS data_inscricao
        FROM new_sistema_inscricao_PJA i
        INNER JOIN new_sistema_cursos_turmas t 
            ON t.chave = i.chaveturma
        WHERE i.codigousuario = :idUsuario
        ORDER BY i.data_ins DESC
    ";
    $st = $pdo->prepare($sql);
    $st->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $st->execute();
    $turmas = $st->fetchAll(PDO::FETCH_ASSOC);

    // Mapa rápido das chaves já inscritas (para desabilitar no <select>)
    $chavesInscritas = [];
    foreach ($turmas as $t) {
        if (!empty($t['chave_turma'])) {
            $chavesInscritas[$t['chave_turma']] = true;
        }
    }

    // 3) Carregar todas as turmas disponíveis (para popular o seletor)
    //    Ajuste o ORDER BY conforme quiser (por título, por curso, etc.)
    $sqlAll = "
        SELECT 
            datast    AS dataturma,
            codcursost    AS idcurso,
            codigoturma   AS idturma,
            nometurma  AS titulo_turma,
            chave         AS chave_turma
        FROM new_sistema_cursos_turmas
        ORDER BY dataturma DESC, nometurma ASC
    ";
    $stAll = $pdo->prepare($sqlAll);
    $stAll->execute();
    $todasTurmas = $stAll->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger mb-0">Erro ao carregar turmas do aluno.</div>';
    return;
}

// 4) Cabeçalho
$total = $turmas ? count($turmas) : 0;
?>

<!-- Barra de inscrição em turma -->
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-8">

                <?php require 'usuariosv1.0/require_MsgsWhatsApp.php'; ?>
                <label class="form-label">Selecionar turma</label>
                <select id="selectTurma" class="form-select">
                    <option value="">— escolha uma turma —</option>
                    <?php foreach ($todasTurmas as $opt):
                        $titulo = $opt['titulo_turma'] ?? 'Turma sem título';
                        $chave  = $opt['chave_turma']  ?? '';
                        $idTurma = (string)($opt['idturma'] ?? '');
                        $idCurso = (string)($opt['idcurso'] ?? '');
                        $jaInscrito = $chavesInscritas[$chave] ?? false;

                        // Vamos enviar pelo AJAX a chave (única) e ids criptografados (opcionalmente úteis)
                        $encIdTurma = encrypt($idTurma, $action = 'e');
                        $encIdCurso = encrypt($idCurso, $action = 'e');

                        $rotulo = $titulo . (!empty($chave) ? " — {$chave}" : '');
                    ?>
                        <option value="<?= htmlspecialchars($chave) ?>"
                            data-idturma="<?= htmlspecialchars($encIdTurma) ?>"
                            data-idcurso="<?= htmlspecialchars($encIdCurso) ?>"
                            <?= $jaInscrito ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($rotulo) ?><?= $jaInscrito ? ' (já inscrito)' : '' ?>
                            <?= htmlspecialchars($idTurma); ?>-<?= htmlspecialchars($idCurso); ?> </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Turmas já inscritas aparecem desabilitadas.</div>
            </div>
            <div class="col-md-4 d-grid d-md-flex justify-content-md-end">
                <button id="btnInscrever" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i> Inscrever aluno
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cabeçalho da lista -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-people-fill me-2"></i> Turmas do Aluno
    </h5>
    <span class="badge bg-primary"><?= (int)$total ?> turma<?= $total === 1 ? '' : 's' ?></span>
</div>

<?php if (!$turmas): ?>
    <div class="alert alert-info">Nenhuma turma encontrada para este aluno.</div>
    <?php return; ?>
<?php endif; ?>

<!-- Controles de renovação -->
<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="fw-semibold">Adicionar dias ao prazo:</div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPrazo" id="dias2" value="2">
                <label class="form-check-label" for="dias2">2</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPrazo" id="dias30" value="30" checked>
                <label class="form-check-label" for="dias30">30</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPrazo" id="dias366" value="366">
                <label class="form-check-label" for="dias366">366</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="diasPrazo" id="dias1830" value="1830">
                <label class="form-check-label" for="dias1830">1830</label>
            </div>
            <div class="text-muted small">Selecione um valor e use o botão “Renovar” em uma turma.</div>
        </div>
    </div>
</div>


<ul class="list-group" id="listaTurmasAluno">
    <?php foreach ($turmas as $t):
        $titulo  = $t['titulo_turma'] ?? 'Turma sem título';
        $chave   = $t['chave_turma']  ?? '';
        $dataIns = $t['data_inscricao'] ?? '';
        $dataprazo = $t['dataprazo'] ?? '';
        $dataFmt = '';
        if (!empty($dataIns)) {
            try {
                $dataFmt = (new DateTime($dataIns))->format('d/m/Y');
            } catch (Exception $e) {
                $dataFmt = htmlspecialchars($dataIns);
            }
            $dataprazo = (new DateTime($dataprazo))->format('d/m/Y');
        }
        $encIdCurso = encrypt((string)($t['idcurso'] ?? ''), $action = 'e');
        $encIdTurma = encrypt((string)($t['idturma'] ?? ''), $action = 'e');
        $encChave   = encrypt($chave, $action = 'e'); // para segurança no AJAX
    ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div class="me-3">
                <div class="fw-semibold">
                    <a href="cursos_TurmasAlunos.php?id=<?= urlencode($encIdCurso) ?>&tm=<?= urlencode($encIdTurma) ?>">
                        <?= htmlspecialchars($titulo) ?> T:<?= $idTurma; ?>-C:<?= $idCurso; ?>
                    </a>
                </div>
                <small class="text-muted">
                    Chave: <span class="font-monospace"><?= htmlspecialchars($chave) ?></span>
                    <?php if ($dataFmt): ?> • Inscrito em <?= $dataFmt ?><?php endif; ?>
                        <?php if ($dataprazo): ?> • Prazo: em <?= $dataprazo ?><?php endif; ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="cursos_TurmasAlunos.php?id=<?= urlencode($encIdCurso) ?>&tm=<?= urlencode($encIdTurma) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i>turma
                </a>
                <?php
                $encChave = encrypt($chave, $action = 'e'); // já existente no seu fluxo de exclusão
                ?>
                <button type="button" class="btn btn-sm btn-outline-success"
                    onclick="renovarInscricao('<?= $encChave ?>', '<?= htmlspecialchars($titulo) ?>')">
                    <i class="bi bi-arrow-repeat me-1"></i> Renovar
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="navigator.clipboard?.writeText('<?= htmlspecialchars($chave) ?>')">
                    <i class="bi bi-clipboard me-1"></i>Chave
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="excluirInscricao('<?= $encChave ?>', '<?= htmlspecialchars($titulo) ?>')">
                    <i class="bi bi-trash me-1"></i>
                </button>
            </div>
        </li>
    <?php endforeach; ?>
</ul>


<script>
    // Inscrição via AJAX
    (function() {
        const btn = document.getElementById('btnInscrever');
        const sel = document.getElementById('selectTurma');
        if (!btn || !sel) return;

        btn.addEventListener('click', function() {
            const opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) {
                alert('Selecione uma turma.');
                return;
            }
            const chaveTurma = opt.value; // chave única da turma (string)
            const idTurmaEnc = opt.getAttribute('data-idturma') || '';
            const idCursoEnc = opt.getAttribute('data-idcurso') || '';
            const idUsuarioEnc = '<?= htmlspecialchars($idUsuarioEnc) ?>';

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Inscrevendo...';

            fetch('usuariosv1.0/ajax_inscreverAlunoTurma.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        idUsuario: idUsuarioEnc,
                        chaveTurma: chaveTurma,
                        idTurma: idTurmaEnc, // opcional
                        idCurso: idCursoEnc // opcional
                    })
                })
                .then(r => r.json())
                .then(json => {
                    if (json && json.ok) {
                        // Atualiza a página para refletir a nova inscrição
                        location.reload();
                    } else {
                        alert(json?.msg || 'Não foi possível concluir a inscrição.');
                    }
                })
                .catch(() => {
                    alert('Erro na comunicação com o servidor.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-person-plus me-1"></i> Inscrever aluno';
                });
        });
    })();
</script>

<script>
    function excluirInscricao(chaveEnc, tituloTurma) {
        if (!confirm(`Tem certeza que deseja excluir a inscrição na turma:\n${tituloTurma}?`)) {
            return;
        }

        fetch('usuariosv1.0/ajax_excluirInscricaoAluno.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    idUsuario: '<?= htmlspecialchars($_GET['idUsuario']) ?>', // ainda criptografado
                    chaveTurma: chaveEnc
                })
            })
            .then(r => r.json())
            .then(json => {
                if (json && json.ok) {
                    location.reload();
                } else {
                    alert(json?.msg || 'Não foi possível excluir a inscrição.');
                }
            })
            .catch(() => {
                alert('Erro na comunicação com o servidor.');
            });
    }
</script>

<script>
    function getDiasSelecionados() {
        const sel = document.querySelector('input[name="diasPrazo"]:checked');
        return sel ? parseInt(sel.value, 10) : NaN;
    }

    function renovarInscricao(chaveEnc, tituloTurma) {
        const dias = getDiasSelecionados();
        if (![2, 30, 366, 1830].includes(dias)) {
            alert('Selecione uma quantidade de dias válida (2, 30, 366 ou 1830).');
            return;
        }

        if (!confirm(`Renovar a inscrição na turma:\n${tituloTurma}\n\nAdicionar ${dias} dia(s) ao prazo?`)) {
            return;
        }

        const btn = event?.currentTarget;
        if (btn) {
            btn.disabled = true;
            const old = btn.innerHTML;
            btn.dataset.old = old;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Renovando...';
        }

        fetch('usuariosv1.0/ajax_renovarInscricaoAluno.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({
                    idUsuario: '<?= htmlspecialchars($_GET['idUsuario']) ?>', // criptografado
                    chaveTurma: chaveEnc,
                    dias: String(dias)
                })
            })
            .then(r => r.json())
            .then(json => {
                if (json && json.ok) {
                    location.reload();
                } else {
                    alert(json?.msg || 'Não foi possível renovar a inscrição.');
                }
            })
            .catch(() => {
                alert('Erro na comunicação com o servidor.');
            })
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.old || '<i class="bi bi-arrow-repeat me-1"></i> Renovar';
                }
            });
    }
</script>