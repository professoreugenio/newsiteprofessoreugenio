<?php
if (!defined('BASEPATH')) define('BASEPATH', true);
if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

/* ----------
   Helpers
---------- */
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function fotoAlunoUrl(string $pasta = null, string $img = null): string
{
    $p = trim($pasta ?? '');
    $f = trim($img ?? '');
    if ($f === '' || $f === 'usuario.jpg') {
        return 'https://professoreugenio.com/fotos/usuarios/usuario.jpg';
    }
    return "https://professoreugenio.com/fotos/usuarios/{$p}/{$f}";
}

/* --------------------
   Filtros básicos
-------------------- */
$db = config::connect();

/* Anos com base na data de inscrição */
$stmtAnos = $db->query("
    SELECT DISTINCT YEAR(i.data_ins) AS ano
    FROM new_sistema_inscricao_PJA i
    WHERE i.data_ins IS NOT NULL
    ORDER BY ano DESC
");
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);

/* Turmas com base nas inscrições */
$stmtTurmas = $db->query("
    SELECT t.chave, t.nometurma
    FROM new_sistema_cursos_turmas t
    INNER JOIN new_sistema_inscricao_PJA i ON i.chaveturma = t.chave
    GROUP BY t.chave, t.nometurma
    ORDER BY t.nometurma
");
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

/* Parâmetros GET */
$anoSel   = isset($_GET['ano'])   ? max(0, (int)$_GET['ano']) : 0;
$turmaSel = isset($_GET['turma']) ? (string)$_GET['turma']    : '';
$busca    = isset($_GET['busca']) ? trim((string)$_GET['busca']) : '';

/* -----------------------------
   Montagem do WHERE (somente vencidos)
------------------------------*/
$where   = [];
$params  = [];

/* Apenas quem tem pelo menos uma inscrição vencida */
$where[] = "i.dataprazosi IS NOT NULL AND i.dataprazosi < CURDATE()";

/* Busca por nome/e-mail/celular (case-insensitive) */
if ($busca !== '') {
    $where[] = "(c.nome LIKE :busca OR c.email LIKE :busca OR c.celular LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

/* Filtro por Ano (na data de inscrição) */
if ($anoSel) {
    $where[] = "YEAR(i.data_ins) = :ano";
    $params[':ano'] = $anoSel;
}

/* Filtro por Turma (chave da turma) */
if ($turmaSel !== '') {
    $where[] = "i.chaveturma = :turma";
    $params[':turma'] = $turmaSel;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* ----------------
   Paginação
----------------- */
$pagina    = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 50;
$offset    = ($pagina - 1) * $porPagina;

/* -------------------------------
   Total (DISTINCT alunos vencidos)
-------------------------------- */
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT c.codigocadastro
        FROM new_sistema_inscricao_PJA i
        INNER JOIN new_sistema_cadastro c ON c.codigocadastro = i.codigousuario
        $whereSql
        GROUP BY c.codigocadastro
    ) X
";
$stmtCount = $db->prepare($sqlCount);
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalAlunos  = (int)$stmtCount->fetchColumn();
$totalPaginas = max(1, (int)ceil($totalAlunos / $porPagina));

/* ----------------------------------------------
   Consulta principal (agregada por ALUNO)
   - qtd_ins: total de inscrições do aluno (com os filtros aplicados)
   - data_ins_recente: última data de inscrição (com filtros)
   - dataprazo_recente: último prazo vencido (com filtros)
   - turmas_nomes: nomes das turmas (para informar no badge, se quiser)
   - tem_atendimento: 1 se houver qualquer atendimento para o aluno
----------------------------------------------- */
$sqlAlunos = "
    SELECT
        c.codigocadastro                      AS idaluno,
        c.nome,
        c.email,
        c.celular,
        c.pastasc,
        c.imagem200,
        COUNT(*)                              AS qtd_ins,
        MAX(i.data_ins)                       AS data_ins_recente,
        MAX(i.dataprazosi)                    AS dataprazo_recente,
        GROUP_CONCAT(DISTINCT t.nometurma ORDER BY t.nometurma SEPARATOR ', ') AS turmas_nomes,
        CASE WHEN COUNT(aa.codigoatendimento) > 0 THEN 1 ELSE 0 END          AS tem_atendimento
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c       ON c.codigocadastro = i.codigousuario
    INNER JOIN new_sistema_cursos_turmas t  ON t.chave = i.chaveturma
    LEFT  JOIN a_aluno_atendimento aa       ON aa.idaluno = c.codigocadastro
    $whereSql
    GROUP BY c.codigocadastro, c.nome, c.email, c.celular, c.pastasc, c.imagem200
    ORDER BY dataprazo_recente ASC, data_ins_recente DESC
    LIMIT :limite OFFSET :offset
";
$stmt = $db->prepare($sqlAlunos);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

/* Cursos (dropdown lateral direita) — mesmo que sua base */
$stmtCursos = $db->query("
    SELECT DISTINCT t.codigoturma, t.nometurma
    FROM new_sistema_cursos_turmas t
    INNER JOIN new_sistema_inscricao_PJA i ON i.chaveturma = t.chave
    ORDER BY t.nometurma
");
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

/* -----------------
   Link atendimento
------------------ */

?>

<!-- FILTROS TOPO -->
<form method="get" class="row row-cols-lg-auto g-2 align-items-center mb-3">
    <div class="col">
        <select name="ano" class="form-select" onchange="this.form.submit()">
            <option value="0">Todos os Anos</option>
            <?php foreach ($anos as $ano): ?>
                <option value="<?= (int)$ano ?>" <?= ($anoSel == (int)$ano ? 'selected' : '') ?>><?= (int)$ano ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select name="turma" class="form-select" onchange="this.form.submit()">
            <option value="">Todas as Turmas</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?= h($t['chave']) ?>" <?= ($turmaSel === $t['chave'] ? 'selected' : '') ?>>
                    <?= h($t['nometurma']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <input type="text" name="busca" class="form-control" style="max-width:320px"
            placeholder="Buscar por nome, e-mail ou celular..." value="<?= h($busca) ?>">
    </div>
    <div class="col">
        <button class="btn btn-outline-secondary" type="submit">
            <i class="bi bi-search"></i> Filtrar
        </button>
    </div>
</form>

<!-- PAGINAÇÃO SUPERIOR -->
<?php if ($totalPaginas > 1): ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <?php if ($pagina > 1): ?>
                <a class="btn btn-outline-secondary btn-sm me-2"
                    href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $pagina - 1]))) ?>">
                    <i class="bi bi-chevron-left"></i> Voltar
                </a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $i]))) ?>"
                    class="btn btn-sm <?= ($i == $pagina ? 'btn-primary' : 'btn-outline-primary') ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a class="btn btn-outline-secondary btn-sm ms-2"
                    href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $pagina + 1]))) ?>">
                    Avançar <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <!-- Dropdown lateral direita: Cursos -->
        <div>
            <div class="dropdown">
                <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownCursos" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list"></i> Cursos
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownCursos">
                    <?php foreach ($cursos as $curso): ?>
                        <li><span class="dropdown-item"><?= h($curso['nometurma']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- LISTA EM CARDS (ALUNOS VENCIDOS) -->
<div class="row g-3 mb-4">
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>

        <?php

        $Idenc = encrypt($row['idaluno'], $action = 'e');
        $linkAtendimentoFixo = 'alunoAtendimento.php?idUsuario='. $Idenc;

        ?>
        <?php
        $foto     = fotoAlunoUrl($row['pastasc'] ?? '', $row['imagem200'] ?? '');
        $nome     = trim($row['nome'] ?? '');
        $nomeExib = h(implode(' ', array_slice(explode(' ', $nome), 0, 2)));
        $qtdIns   = (int)($row['qtd_ins'] ?? 0);

        $dtInsc   = $row['data_ins_recente'] ? date('d/m/Y', strtotime($row['data_ins_recente'])) : '--/--/----';
        $dtPrazo  = $row['dataprazo_recente'] ? date('d/m/Y', strtotime($row['dataprazo_recente'])) : '--/--/----';
        $temAt    = (int)($row['tem_atendimento'] ?? 0) === 1;

        $celular  = preg_replace('/\D/', '', (string)($row['celular'] ?? ''));
        if ($celular !== '' && substr($celular, 0, 2) !== '55') $celular = '55' . $celular;
        $waLink   = $celular ? ('https://wa.me/' . $celular) : '';
        ?>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 <?= $temAt ? 'border-success' : '' ?>">
                <div class="card-body d-flex align-items-center p-3">
                    <img src="<?= h($foto) ?>" width="56" height="56"
                        class="rounded-circle shadow border me-3" style="object-fit:cover;" alt="Foto">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <!-- Nome linkando para a página solicitada -->
                            <a href="<?= h($linkAtendimentoFixo) ?>"
                                class="fw-bold fs-5 text-decoration-none text-dark">
                                <?= $nomeExib ?>
                            </a>
                            <!-- Destaque Atendimento -->
                            <?php if ($temAt): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-chat-dots me-1"></i> Atendimento
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Sem atendimento
                                </span>
                            <?php endif; ?>

                            <!-- Vencido sempre (por definição da lista) -->
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-x-octagon me-1"></i> Vencido
                            </span>
                        </div>

                        <div class="small mt-1 text-muted">
                            <i class="bi bi-hash me-1"></i><?= $qtdIns ?> inscrição<?= $qtdIns === 1 ? '' : 'es' ?>
                            <span class="mx-1">•</span>
                            <i class="bi bi-calendar-check me-1"></i> Inscrição: <?= $dtInsc ?>
                            <span class="mx-1">•</span>
                            <i class="bi bi-hourglass-bottom me-1"></i> Prazo: <strong class="text-danger"><?= $dtPrazo ?></strong>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                            <?php if (!empty($row['turmas_nomes'])): ?>
                                <span class="badge bg-light text-dark px-2 py-1">
                                    <i class="bi bi-mortarboard me-1"></i><?= h($row['turmas_nomes']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($row['email'])): ?>
                                <span class="badge bg-light text-dark px-2 py-1">
                                    <i class="bi bi-envelope me-1"></i><?= h($row['email']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($waLink): ?>
                                <a class="badge bg-success bg-opacity-25 text-success px-2 py-1 text-decoration-none"
                                    href="<?= h($waLink) ?>" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary px-2 py-1">
                                    <i class="bi bi-phone-x me-1"></i> WhatsApp não informado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acesso rápido atendimento -->
                    <div class="ms-3">
                        <a href="<?= h($linkAtendimentoFixo) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-journal-text me-1"></i> Atendimento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- PAGINAÇÃO INFERIOR -->
<?php if ($totalPaginas > 1): ?>
    <div class="d-flex justify-content-center my-4">
        <?php if ($pagina > 1): ?>
            <a class="btn btn-outline-secondary btn-sm me-2"
                href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $pagina - 1]))) ?>">
                <i class="bi bi-chevron-left"></i> Voltar
            </a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $i]))) ?>"
                class="btn btn-sm <?= ($i == $pagina ? 'btn-primary' : 'btn-outline-primary') ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
            <a class="btn btn-outline-secondary btn-sm ms-2"
                href="?<?= h(http_build_query(array_merge($_GET, ['pagina' => $pagina + 1]))) ?>">
                Avançar <i class="bi bi-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>