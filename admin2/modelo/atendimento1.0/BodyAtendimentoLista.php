<?php
// BodyAtendimentoLista.php — LISTA GERAL DE ATENDIMENTOS
// Contexto: $con (PDO) já disponível via include principal. NÃO adicionar <html>, <head>, <body>.

// -----------------------
// Helpers locais
// -----------------------
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('fotoAlunoUrl')) {
    function fotoAlunoUrl(?string $pasta, ?string $img): string
    {
        $p = trim((string)$pasta);
        $f = trim((string)$img);
        if ($f === '' || $f === 'usuario.jpg') {
            return 'https://professoreugenio.com/fotos/usuarios/usuario.jpg';
        }
        return "https://professoreugenio.com/fotos/usuarios/{$p}/{$f}";
    }
}
if (!function_exists('dtBr')) {
    function dtBr(?string $ymd): string
    {
        if (!$ymd || $ymd === '0000-00-00') return '--/--/----';
        $ts = strtotime($ymd);
        return $ts ? date('d/m/Y', $ts) : '--/--/----';
    }
}
if (!function_exists('nomeDiaSemana')) {
    function nomeDiaSemana(?string $ymd): string
    {
        if (!$ymd) return '';
        $ts = strtotime($ymd);
        if (!$ts) return '';
        $map = [1 => 'segunda-feira', 2 => 'terça-feira', 3 => 'quarta-feira', 4 => 'quinta-feira', 5 => 'sexta-feira', 6 => 'sábado', 7 => 'domingo'];
        $n = (int)date('N', $ts);
        return $map[$n] ?? '';
    }
}
if (!function_exists('calcIdade')) {
    function calcIdade(?string $ymd): ?int
    {
        if (!$ymd || $ymd === '0000-00-00') return null;
        try {
            $nasc = new DateTime($ymd);
            $hoje = new DateTime('today');
            return (int)$nasc->diff($hoje)->y;
        } catch (Throwable $e) {
            return null;
        }
    }
}

// -----------------------
// Parâmetros de filtro (MÊS OU DIA)
// -----------------------
// Prioridade: se "dia" informado (YYYY-MM-DD), ignora "mes".
$hoje = date('Y-m-d');
$mesAtual = date('Y-m'); // YYYY-MM

$dia = isset($_GET['dia']) ? trim($_GET['dia']) : '';
$mes = isset($_GET['mes']) ? trim($_GET['mes']) : '';

$usarDia = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia);
$usarMes = (bool)preg_match('/^\d{4}-\d{2}$/', $mes);

if (!$usarDia && !$usarMes) {
    // default: mês atual
    $mes = $mesAtual;
    $usarMes = true;
}

// Monta intervalos
$whereData = '';
$params = [];
if ($usarDia) {
    $whereData = ' AND a.dataat = :dia ';
    $params[':dia'] = $dia;
} else {
    // mês: de primeiro dia até primeiro dia do próximo mês (range half-open)
    $ini = $mes . '-01';
    $prox = date('Y-m-d', strtotime($ini . ' +1 month'));
    $whereData = ' AND a.dataat >= :ini AND a.dataat < :prox ';
    $params[':ini'] = $ini;
    $params[':prox'] = $prox;
}

// -----------------------
// Consulta principal
// - Lista atendimentos (a)
// - Aluno (u)
// - Etapa (e)
// - Turma (t) via inscrição mais recente (s) do aluno
// -----------------------
// Estratégia de turma: pega inscrição mais recente do aluno (MAX data_ins) e relaciona por chave com turmas.

if (!empty($_GET['idUsuario'])) {
    $idUsuario = encrypt($_GET['idUsuario'], 'd');
    if (!is_numeric($idUsuario)) {
        throw new Exception("ID de usuário inválido");
    }
    $whereIdUsuario = " AND a.idaluno = :idUsuario ";
    $params[':idUsuario'] = $idUsuario;
} else {
    $whereIdUsuario = "";
}

$sql = "
SELECT
    a.codigoatendimento,
    a.idaluno,
    a.dataat,
    a.horaat,
    a.idetapaaa,
    e.nomeetapa,
    e.ordem,

    u.codigocadastro,
    u.nome AS nome_aluno,
    u.pastasc,
    u.imagem50,
    u.imagem200,
    u.datanascimento_sc,
    u.possuipc,

    t.nometurma
FROM a_aluno_atendimento a
JOIN new_sistema_cadastro u
      ON u.codigocadastro = a.idaluno
LEFT JOIN a_aluno_atendimento_etapas e
      ON e.codigoetapas = a.idetapaaa
LEFT JOIN (
    SELECT s1.codigousuario, s1.chaveturma, s1.data_ins
    FROM new_sistema_inscricao_PJA s1
    JOIN (
        SELECT codigousuario, MAX(data_ins) AS max_data
        FROM new_sistema_inscricao_PJA
        GROUP BY codigousuario
    ) ult
      ON ult.codigousuario = s1.codigousuario AND ult.max_data = s1.data_ins
) s
      ON s.codigousuario = u.codigocadastro
LEFT JOIN new_sistema_cursos_turmas t
      ON t.chave = s.chaveturma
WHERE 1=1
{$whereData}
{$whereIdUsuario}
ORDER BY a.dataat DESC, a.horaat DESC, a.codigoatendimento DESC
";

$stmt = $con->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$qtd  = count($rows);


// paleta para badges de etapa
$palette = ['primary', 'success', 'warning', 'info', 'secondary', 'danger', 'dark'];
$badgeCache = [];
$badgeFor = function (array $r) use (&$badgeCache, $palette) {
    $idEtapa = (int)($r['idetapaaa'] ?? 0);
    if ($idEtapa <= 0) return 'secondary';
    if (isset($badgeCache[$idEtapa])) return $badgeCache[$idEtapa];
    $ordem = isset($r['ordem']) ? max(0, (int)$r['ordem'] - 1) : 0;
    $style = $palette[$ordem % count($palette)];
    return $badgeCache[$idEtapa] = $style;
};

// Para o link de acesso (mensagens), usamos id do aluno + id da etapa da linha
// Ex.: alunoAtendimentoMensagens.php?idUsuario={enc(idAluno)}&idEtapa={enc(idEtapa)}
?>

<!-- Cabeçalho com aluno -->

<?php

// Pega variáveis da sessão (com fallback caso não existam)
$idUsuario = $_SESSION['idUsuario'] ?? '';
$idUrl = $_SESSION['id'] ?? '';
$tm = $_SESSION['tm'] ?? '';
$ts  = $_SESSION['ts'] ?? '';

$linkFinal = "cursos_TurmasAlunos.php?id={$idUrl}&tm={$tm}";
?>

<?php require 'atendimento1.0/requireCabecalhoAtendimento.php' ?>

<!-- Filtros -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <form class="row gy-2 gx-2 align-items-end" method="get">
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Filtrar por mês</label>
                <input type="month" name="mes" class="form-control" value="<?= h($usarMes ? $mes : '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-1">Ou por dia</label>
                <input type="date" name="dia" class="form-control" value="<?= h($usarDia ? $dia : '') ?>">
            </div>
            <div class="col-12 col-md-3 d-grid d-md-block">
                <button class="btn btn-primary mt-2 mt-md-0">
                    <i class="bi bi-funnel me-1"></i> Aplicar
                </button>
                <a href="?mes=<?= h(date('Y-m')) ?>" class="btn btn-outline-secondary ms-md-2 mt-2 mt-md-0">
                    <i class="bi bi-x-circle me-1"></i> Limpar
                </a>

                <a href="<?= htmlspecialchars($linkFinal) ?>"  class="btn btn-success">
                    Acessar Turma
                </a>
            </div>
            <div class="col-12 col-md text-md-end">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-journal-text me-1"></i><?= (int)$qtd ?> atendimento<?= $qtd === 1 ? '' : 's' ?>
                </span>
            </div>
        </form>
    </div>
</div>

<?php if ($qtd === 0): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Nenhum atendimento encontrado para o período selecionado.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:44px;"></th>
                            <th>Aluno</th>
                            <th>Turma</th>
                            <th>PC</th>
                            <th>Idade</th>
                            <th>Data</th>
                            <th>Dia</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th class="text-end" style="width:70px;">Acesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r):
                            $idAluno = (int)$r['idaluno'];
                            $idEnc   = function_exists('encrypt') ? encrypt((string)$idAluno, 'e') : (string)$idAluno;

                            $idEtapa = (int)($r['idetapaaa'] ?? 0);
                            $idEtapaEnc = $idEtapa > 0
                                ? (function_exists('encrypt') ? encrypt((string)$idEtapa, 'e') : (string)$idEtapa)
                                : '';

                            $linkMsg = 'alunoAtendimentoMensagens.php?idUsuario=' . urlencode($idEnc)
                                . ($idEtapaEnc !== '' ? '&idEtapa=' . urlencode($idEtapaEnc) : '');

                            $nome    = trim($r['nome_aluno'] ?? '');
                            $turma   = trim($r['nometurma'] ?? '—');
                            $pcTxt   = ((int)($r['possuipc'] ?? 0) === 1) ? 'Sim' : 'Não';
                            $idade   = calcIdade($r['datanascimento_sc'] ?? null);
                            $idadeTx = $idade !== null ? $idade . ' anos' : '—';

                            $dataYmd = $r['dataat'] ?? null;
                            $hora    = $r['horaat'] ?? '';
                            $dataBr  = dtBr($dataYmd);
                            $diaNome = nomeDiaSemana($dataYmd);

                            $badge   = $badgeFor($r);
                            $nomeEtp = trim($r['nomeetapa'] ?? '');
                            $foto    = fotoAlunoUrl($r['pastasc'] ?? '', ($r['imagem50'] ?: $r['imagem200'] ?? ''));
                        ?>
                            <tr>
                                <td class="text-center">
                                    <img src="<?= h($foto) ?>" alt="Foto" width="36" height="36"
                                        class="rounded-circle border" style="object-fit:cover;">
                                </td>
                                <td>
                                    <?php if ($linkMsg): ?>
                                        <a class="fw-semibold text-decoration-none" href="<?= h($linkMsg) ?>" title="Abrir atendimento do aluno">
                                            <?= h($nome) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="fw-semibold"><?= h($nome) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($turma) ?></td>
                                <td>
                                    <span class="badge <?= $pcTxt === 'Sim' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                                        <?= h($pcTxt) ?>
                                    </span>
                                </td>
                                <td><?= h($idadeTx) ?></td>
                                <td><span class="fw-semibold"><?= h($dataBr) ?></span></td>
                                <td><?= h($diaNome) ?></td>
                                <td><?= h($hora ?: '--:--') ?></td>
                                <td>
                                    <?php if ($idEtapaEnc !== ''): ?>
                                        <a href="<?= h($linkMsg) ?>"
                                            class="badge bg-<?= h($badge) ?>-subtle text-<?= h($badge) ?> border border-<?= h($badge) ?>-subtle text-decoration-none"
                                            title="Abrir atendimento: <?= h($nomeEtp ?: 'Etapa') ?>">
                                            <i class="bi bi-flag me-1"></i><?= h($nomeEtp ?: 'Etapa') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-flag me-1"></i> Não informado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($linkMsg): ?>
                                        <a class="btn btn-outline-primary btn-sm" href="<?= h($linkMsg) ?>" title="Abrir mensagens">
                                            <i class="bi bi-box-arrow-in-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>