<?php
// ====== Filtros ======
$anoSel    = isset($_GET['ano'])   ? (int)$_GET['ano'] : 0;
$cursoSel  = isset($_GET['curso']) ? (int)$_GET['curso'] : 0;

// ====== Opções de Ano (a partir das datas das turmas) ======
$sqlAnos = "
    SELECT DISTINCT COALESCE(YEAR(datainiciost), YEAR(datast)) AS ano
    FROM new_sistema_cursos_turmas
    WHERE COALESCE(datainiciost, datast) IS NOT NULL
    ORDER BY ano DESC
";
$anos = config::connect()->query($sqlAnos)->fetchAll(PDO::FETCH_COLUMN);

// ====== Opções de Curso ======
$sqlCursos = "
    SELECT codigocategorias, nome
    FROM new_sistema_categorias_PJA
    ORDER BY nome
";
$cursos = config::connect()->query($sqlCursos)->fetchAll(PDO::FETCH_ASSOC);

// ====== Monta WHERE dinamicamente ======
$where = [];
$params = [];

if ($anoSel) {
    $where[] = "COALESCE(YEAR(t.datainiciost), YEAR(t.datast)) = :anoSel";
    $params[':anoSel'] = $anoSel;
}
if ($cursoSel) {
    $where[] = "t.codcursost = :cursoSel";
    $params[':cursoSel'] = $cursoSel;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ====== Consulta principal: turmas + curso + contagem de alunos ======
$sqlTurmas = "
    SELECT
        t.codigoturma,
        t.codcursost,
        t.nometurma,
        t.datainiciost,
        t.datafimst,
        t.datast,
        t.chave,
        t.comercial_stc,
        t.institucional,
        c.nome AS curso_nome,
        COUNT(i.codigousuario) AS qtd_alunos
    FROM new_sistema_cursos_turmas t
    LEFT JOIN new_sistema_categorias_PJA c
           ON c.codigocategorias = t.codcursost
    LEFT JOIN new_sistema_inscricao_PJA i
           ON i.chaveturma = t.chave
    $whereSql
    GROUP BY
        t.codigoturma, t.codcursost, t.nometurma, t.datainiciost, t.datafimst,
        t.datast, t.chave, t.comercial_stc, t.institucional, c.nome
    ORDER BY
        t.datast DESC, c.nome ASC, t.nometurma ASC
";
$stmt = config::connect()->prepare($sqlTurmas);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper para mês PT-BR
function mes_ptbr($data)
{
    if (!$data) return '—';
    $meses = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
    ];
    $ts = strtotime($data);
    $m = (int)date('n', $ts);
    return $meses[$m] . ' de ' . date('Y', $ts);
}
?>

<!-- Filtros -->
<form method="get" class="row row-cols-lg-auto g-2 align-items-end mb-3">
    <div class="col">
        <label class="form-label mb-0"><i class="bi bi-calendar3 me-1"></i>Ano</label>
        <select name="ano" class="form-select" onchange="this.form.submit()">
            <option value="0">Todos os anos</option>
            <?php foreach ($anos as $ano): ?>
                <option value="<?= (int)$ano ?>" <?= $anoSel == $ano ? 'selected' : '' ?>>
                    <?= (int)$ano ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <label class="form-label mb-0"><i class="bi bi-mortarboard me-1"></i>Curso</label>
        <select name="curso" class="form-select" onchange="this.form.submit()">
            <option value="0">Todos os cursos</option>
            <?php foreach ($cursos as $cur): ?>
                <option value="<?= (int)$cur['codigocategorias'] ?>" <?= $cursoSel == (int)$cur['codigocategorias'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cur['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($anoSel || $cursoSel): ?>
        <div class="col">
            <a href="?ano=0&curso=0" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpar filtros
            </a>
        </div>
    <?php endif; ?>
</form>

<?php if (empty($turmas)): ?>
    <div class="alert alert-info">Nenhuma turma encontrada com os filtros selecionados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="min-width:320px;">Turma</th>
                    <th style="min-width:220px;">Curso</th>
                    <th class="text-center" style="width:160px;">Alunos</th>
                    <th class="text-center" style="width:180px;">Perfil</th>
                    <th style="width:220px;">Mês do curso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turmas as $t): ?>
                    <?php
                    // Base para mês do curso (usa início; se vazio, usa datast)
                    $dataBaseMes = $t['datainiciost'] ?: $t['datast'];
                    $mesCurso = mes_ptbr($dataBaseMes);

                    // Link parâmetros (use encrypt se necessário)
                    $idParam = $t['codcursost'];
                    $tmParam = $t['chave'];
                    // Se você usa criptografia:
                    // $idParam = encrypt($t['codcursost'], 'e');
                    // $tmParam = encrypt($t['chave'], 'e');

                    $idEnc = encrypt($t['codcursost'], 'e');
                    $tmEnc = encrypt($t['codigoturma'], 'e');

                    $urlTurma = "cursos_TurmasAlunos.php?id={$idEnc}&tm={$tmEnc}";
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <a  href="<?= htmlspecialchars($urlTurma) ?>" class="text-decoration-none">
                                <i class="bi bi-collection-play me-2 text-primary"></i>
                                <?= htmlspecialchars($t['nometurma'] ?: '—') ?>
                            </a>
                            <div class="small text-muted">
                                Início: <?= $t['datainiciost'] ? date('d/m/Y', strtotime($t['datainiciost'])) : '—' ?> |
                                Cadastro: <?= $t['datast'] ? date('d/m/Y', strtotime($t['datast'])) : '—' ?>
                            </div>
                        </td>
                        <td class="text-muted">
                            <?= htmlspecialchars($t['curso_nome'] ?: '—') ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <?= (int)$t['qtd_alunos'] ?> aluno<?= ((int)$t['qtd_alunos'] === 1 ? '' : 's') ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ((int)$t['comercial_stc'] === 1): ?>
                                <span class="badge bg-success"><i class="bi bi-cash-coin me-1"></i>Comercial</span>
                            <?php endif; ?>
                            <?php if ((int)$t['institucional'] === 1): ?>
                                <span class="badge bg-info text-dark ms-1"><i class="bi bi-building me-1"></i>Institucional</span>
                            <?php endif; ?>
                            <?php if ((int)$t['comercial_stc'] !== 1 && (int)$t['institucional'] !== 1): ?>
                                <span class="badge bg-light text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="bi bi-calendar3 me-1"></i><?= $mesCurso ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="5">
                        <small class="text-muted">
                            Ordenado por <b>data da turma (datast)</b> (desc) e <b>nome do curso</b> (asc).
                            O “Mês do curso” usa <b>datainiciost</b> (se existir) ou <b>datast</b>.
                        </small>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>