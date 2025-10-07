<?php
// BodyAtendimentoLista.php
// Requisitos no contexto: $con (PDO) já disponível via include principal.
// NÃO incluir <html>, <head>, <body> aqui (página modulada).

/* -----------------------
   Helpers (locais)
------------------------ */
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
        // 1 (Mon) a 7 (Sun)
        $n = (int)date('N', $ts);
        $map = [
            1 => 'segunda-feira',
            2 => 'terça-feira',
            3 => 'quarta-feira',
            4 => 'quinta-feira',
            5 => 'sexta-feira',
            6 => 'sábado',
            7 => 'domingo',
        ];
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
        } catch (Exception $e) {
            return null;
        }
    }
}

/* -----------------------
   Captura de parâmetro
------------------------ */
$idAluno = 0;
$idParam = $_GET['id'] ?? $_GET['idUsuario'] ?? '';

if ($idParam !== '') {
    // Tenta decrypt se função existir e parecer base64-like
    if (function_exists('encrypt') && preg_match('/^[A-Za-z0-9+\/=]+$/', $idParam)) {
        $dec = @encrypt($idParam, 'd');
        if (ctype_digit((string)$dec)) {
            $idAluno = (int)$dec;
        }
    }
    // Caso não tenha decryptado, tenta como inteiro puro
    if ($idAluno === 0 && ctype_digit((string)$idParam)) {
        $idAluno = (int)$idParam;
    }
}

if ($idAluno <= 0) {
    echo '<div class="alert alert-warning">Aluno não identificado.</div>';
    return;
}

/* -----------------------
   Consulta: aluno
------------------------ */
$sqlAluno = "
  SELECT codigocadastro, nome, pastasc, imagem200, imagem50,
         datanascimento_sc, possuipc
  FROM new_sistema_cadastro
  WHERE codigocadastro = :id
  LIMIT 1
";
$stmtA = $con->prepare($sqlAluno);
$stmtA->bindValue(':id', $idAluno, PDO::PARAM_INT);
$stmtA->execute();
$aluno = $stmtA->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    echo '<div class="alert alert-danger">Aluno não encontrado.</div>';
    return;
}

$nomeAluno   = trim($aluno['nome'] ?? '');
$fotoAluno   = fotoAlunoUrl($aluno['pastasc'] ?? '', $aluno['imagem200'] ?? '');
$nascYmd     = $aluno['datanascimento_sc'] ?? null;
$nascBr      = dtBr($nascYmd);
$idade       = calcIdade($nascYmd);
$possuipcRaw = isset($aluno['possuipc']) ? (int)$aluno['possuipc'] : null;
$possuipcTxt = ($possuipcRaw === 1) ? 'Sim' : 'Não';

// Monta id enc para links
$idEnc = (function_exists('encrypt') ? encrypt((string)$idAluno, 'e') : (string)$idAluno);

/* -----------------------
   Consulta: atendimentos (com tipo)
------------------------ */
$sqlAt = "
  SELECT a.codigoatendimento,
         a.dataat,
         a.horaat,
         a.idetapaaa,
         e.nomeetapa,
         e.ordem
  FROM a_aluno_atendimento a
  LEFT JOIN a_aluno_atendimento_etapas e
         ON e.codigoetapas = a.idetapaaa
  WHERE a.idaluno = :id
  ORDER BY a.dataat DESC, a.horaat DESC
";
$stmtAt = $con->prepare($sqlAt);
$stmtAt->bindValue(':id', $idAluno, PDO::PARAM_INT);
$stmtAt->execute();
$atendimentos = $stmtAt->fetchAll(PDO::FETCH_ASSOC);

$qtdAt = count($atendimentos);

/* -----------------------
   Paleta de cores p/ badges
   (tenta por codigoetapas; fallback cíclico)
------------------------ */
$palette = ['primary', 'success', 'warning', 'info', 'secondary', 'danger', 'dark'];
$badgeCache = []; // codigoetapas => style
function badgeStyleForEtapa(array $row, array &$badgeCache, array $palette): string
{
    $idEtapa = (int)($row['idetapaaa'] ?? 0);
    if ($idEtapa <= 0) return 'secondary';
    if (isset($badgeCache[$idEtapa])) return $badgeCache[$idEtapa];
    // Se tiver 'ordem', usa como índice preferencial
    $ordem = isset($row['ordem']) ? max(0, (int)$row['ordem'] - 1) : 0;
    $style = $palette[$ordem % count($palette)];
    return $badgeCache[$idEtapa] = $style;
}
?>

<!-- Cabeçalho com aluno -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center">
            <img src="<?= h($fotoAluno) ?>" alt="Foto do aluno" width="64" height="64"
                class="rounded-circle border shadow-sm me-3" style="object-fit:cover;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><?= h($nomeAluno) ?></h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-journal-text me-1"></i>
                        <?= $qtdAt ?> atendimento<?= $qtdAt === 1 ? '' : 's' ?>
                    </span>
                </div>
                <div class="text-muted small mt-1 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-cake me-1"></i>
                        Nascimento: <?= h($nascBr) ?><?= $idade !== null ? ' • ' . (int)$idade . ' anos' : '' ?>
                    </span>
                    <span class="badge <?= $possuipcTxt === 'Sim' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                        <i class="bi bi-pc-display me-1"></i> Possui PC: <strong class="ms-1"><?= h($possuipcTxt) ?></strong>
                    </span>
                </div>
            </div>

            <!-- Ação: Novo Atendimento -->
            <div class="ms-3">
                <a href="alunoAtendimentoNovo.php?idUsuario=<?= urlencode($idEnc) ?>"
                    class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> Novo Atendimento
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Lista de atendimentos -->
<?php if ($qtdAt === 0): ?>
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-1"></i> Nenhum atendimento registrado para este aluno.
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Data</th>
                            <th>Dia da semana</th>
                            <th>Hora</th>
                            <th>Tipo de atendimento</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atendimentos as $at): ?>
                            <?php
                            $dataYmd = $at['dataat'] ?? null;
                            $hora    = $at['horaat'] ?? '';
                            $dataBr  = dtBr($dataYmd);
                            $diaNome = nomeDiaSemana($dataYmd);

                            $nomeEtapa = trim($at['nomeetapa'] ?? '');
                            $idEtapa   = (int)($at['idetapaaa'] ?? 0);
                            $badge     = badgeStyleForEtapa($at, $badgeCache, $palette);

                            // idEtapa encryptado para o link (se possível)
                            $idEtapaEnc = ($idEtapa > 0)
                                ? (function_exists('encrypt') ? encrypt((string)$idEtapa, 'e') : (string)$idEtapa)
                                : '';

                            $linkMsg = 'alunoAtendimentoMensagens.php?idUsuario=' . urlencode($idEnc)
                                . '&idEtapa=' . urlencode($idEtapaEnc);
                            ?>
                            <tr>
                                <td class="text-center">
                                    <i class="bi bi-chat-dots text-secondary"></i>
                                </td>
                                <td><span class="fw-semibold"><?= h($dataBr) ?></span></td>
                                <td><?= h($diaNome) ?></td>
                                <td><?= h($hora ?: '--:--') ?></td>
                                <td>
                                    <?php if ($idEtapaEnc !== ''): ?>
                                        <a href="<?= h($linkMsg) ?>"
                                            class="badge bg-<?= h($badge) ?>-subtle text-<?= h($badge) ?> border border-<?= h($badge) ?>-subtle text-decoration-none"
                                            title="Abrir atendimento: <?= h($nomeEtapa ?: 'Etapa') ?>">
                                            <i class="bi bi-flag me-1"></i><?= h($nomeEtapa ?: 'Etapa') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-flag me-1"></i> Etapa não informada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($idEtapaEnc !== ''): ?>
                                        <a class="btn btn-outline-primary btn-sm"
                                            href="<?= h($linkMsg) ?>"
                                            title="Abrir mensagens">
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