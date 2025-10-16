<?php
// -----------------------------------------
// Recuperação do usuário a partir do cookie
// -----------------------------------------
$iduser = $nomeuser = $emailuser = $dataanv = '0';
if (!empty($_COOKIE['adminstart']) || !empty($_COOKIE['startusuario'])) {
    $cookie   = !empty($_COOKIE['adminstart']) ? $_COOKIE['adminstart'] : $_COOKIE['startusuario'];
    $decstart = encrypt($cookie, 'd');
    $exp      = explode("&", $decstart);
    $iduser   = $exp[0] ?? '0';
    $nomeuser = $exp[2] ?? '';
    $emailuser = $exp[3] ?? '';
    $dataanv  = "2005-07-01";
}
$addtime = 60 * 60 * 4; // 4h
$duracao = time() + $addtime;

// -----------------------------------------
// LISTA DE ANOS A PARTIR DE new_sistema_cursos_turmas.datast (ASC)
// -----------------------------------------
$sqlAnos = "
    SELECT DISTINCT YEAR(t.datast) AS ano
    FROM new_sistema_cursos_turmas t
    WHERE t.datast IS NOT NULL
    ORDER BY ano ASC
";
$stmtAnos = $con->prepare($sqlAnos);
$stmtAnos->execute();
$anosDisponiveis = array_map('intval', array_column($stmtAnos->fetchAll(PDO::FETCH_ASSOC), 'ano'));

// Se não houver anos, evita erros adiante
$anoMaisAntigo = $anosDisponiveis ? min($anosDisponiveis) : (int)date('Y');
$anoMaisRecente = $anosDisponiveis ? max($anosDisponiveis) : (int)date('Y');

// -----------------------------------------
// PARÂMETRO DO FILTRO (default = ano mais recente de datast)
// -----------------------------------------
$anoParam = isset($_GET['ano']) ? trim($_GET['ano']) : (string)$anoMaisRecente;
$anoEhValido = (ctype_digit($anoParam) && strlen($anoParam) === 4 && in_array((int)$anoParam, $anosDisponiveis, true));

// -----------------------------------------
// FONTE DE TURMAS (deduplicada) + JOIN DE ÚLTIMO ACESSO (sem bloquear resultados)
// - Admin: todas as turmas que tenham inscrição associada (chaveturma), deduplicadas
// - Usuário: somente turmas em que o usuário está inscrito, deduplicadas
// Filtro por ANO usa YEAR(t.datast)
// -----------------------------------------
if (!empty($_COOKIE['adminstart'])) {
    // ADMIN
    $baseJoin = "
    FROM (
        SELECT DISTINCT i.chaveturma
        FROM new_sistema_inscricao_PJA i
    ) ins
    INNER JOIN new_sistema_cursos_turmas t ON t.chave = ins.chaveturma
    LEFT JOIN (
        SELECT idturmara, MAX(datara) AS ultimo_acesso, MAX(horafinalra) AS ultima_hora
        FROM a_site_registraacessos
        GROUP BY idturmara
    ) ra ON ra.idturmara = t.codigoturma
";

    $where = [];
    if ($anoEhValido) {
        $where[] = " YEAR(t.datast) = :ano ";
    }
    $sql = "SELECT t.*, ra.ultimo_acesso, ra.ultima_hora " . $baseJoin
        . (count($where) ? " WHERE " . implode(" AND ", $where) : "")
        . " ORDER BY COALESCE(ra.ultimo_acesso, t.datast) DESC, t.codigoturma DESC
             LIMIT 0, 100"; // limite opcional para admin
    $stmt = $con->prepare($sql);
    if ($anoEhValido) {
        $stmt->bindValue(':ano', (int)$anoParam, PDO::PARAM_INT);
    }
} else {
    // USUÁRIO
    $baseJoin = "
    FROM (
        SELECT DISTINCT i.chaveturma
        FROM new_sistema_inscricao_PJA i
    ) ins
    INNER JOIN new_sistema_cursos_turmas t ON t.chave = ins.chaveturma
    LEFT JOIN (
        SELECT idturmara, MAX(datara) AS ultimo_acesso, MAX(horafinalra) AS ultima_hora
        FROM a_site_registraacessos
        GROUP BY idturmara
    ) ra ON ra.idturmara = t.codigoturma
";

    $where = ["1=1"];
    if ($anoEhValido) {
        $where[] = " YEAR(t.datast) = :ano ";
    }
    $sql = "SELECT t.*, ra.ultimo_acesso, ra.ultima_hora " . $baseJoin
        . (count($where) ? " WHERE " . implode(" AND ", $where) : "")
        . " ORDER BY COALESCE(ra.ultimo_acesso, t.datast) DESC, t.codigoturma DESC
         LIMIT 0, 100";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':idusuario', $iduser, PDO::PARAM_INT);
    if ($anoEhValido) {
        $stmt->bindValue(':ano', (int)$anoParam, PDO::PARAM_INT);
    }
}

$stmt->execute();
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$quant  = count($turmas);
?>

<!-- =========================
     FILTRO VISUAL (ASC)
========================= -->
<form method="get" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <label for="filtroAno" class="form-label mb-0">Filtrar por ano:</label>
        </div>
        <div class="col-auto">
            <select id="filtroAno" name="ano" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($anosDisponiveis as $ano): ?>
                    <option value="<?= (int)$ano; ?>" <?= ($anoParam == (string)$ano ? 'selected' : ''); ?>>
                        <?= (int)$ano; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <small class="text-muted">Intervalo: <?= $anoMaisAntigo; ?> → <?= $anoMaisRecente; ?></small>
        </div>
    </div>
</form>
<div class="cards-container">
    <?php
    // -----------------------------------------
    // RENDERIZAÇÃO DOS CARDS
    // -----------------------------------------
    foreach ($turmas as $value) {
        $idCurso     = $value['codcursost']   ?? '0';
        $codTurma    = $value['codigoturma']  ?? '0';
        $chaveTurma  = $value['chave']        ?? '0';
        $comercial   = $value['comercialt']   ?? '0';
        $dtprazo     = $value['dataprazosi']  ?? '0';
        $ativo       = $value['andamento']    ?? '0';
        $assinante   = $value['renovacaosi']  ?? '0';
        $datast      = $value['datast']       ?? null; // data de criação/início da turma

        // Token
        $tokenturma = implode("&", [$iduser, $nomeuser, $emailuser, $dataanv, $codTurma, $chaveTurma, $duracao, $dtprazo, $comercial]);
        $tokem = encrypt($tokenturma, 'e');

        // Datas de exibição
        // Datas de exibição
        $ultimoAcessoRaw = $value['ultimo_acesso'] ?? null;  // YYYY-mm-dd
        $ultimaHora      = $value['ultima_hora']    ?? null; // HH:ii:ss

        // Helpers de formatação (caso não existam)
        if (!function_exists('formatHoraBr')) {
            function formatHoraBr(?string $hora): string
            {
                if (!$hora) return '';
                $ts = strtotime($hora);
                return $ts ? date('H:i', $ts) : substr($hora, 0, 5); // fallback
            }
        }

        if ($ultimoAcessoRaw) {
            // Ex.: "Último acesso: 21/08/2025 14:37"
            $linhaData = "Último acesso: " . databr($ultimoAcessoRaw);
            $h = formatHoraBr($ultimaHora ?? '');
            if (!empty($h)) $linhaData .= " " . $h;
        } elseif (!empty($datast)) {
            $linhaData = "Criada em: " . databr($datast);
        } else {
            $linhaData = "Sem registro";
        }


        // Capa (sem mkdir em listagem)
        $tipo = 3;
        $stmtFoto = $con->prepare("
        SELECT f.*, c.*
        FROM new_sistema_cursos c
        INNER JOIN new_sistema_midias_fotos_PJA f ON c.pasta = f.pasta
        WHERE f.codpublicacao = :id AND f.tipo = :tipo
        LIMIT 1
    ");
        $stmtFoto->bindValue(":id",   $idCurso, PDO::PARAM_INT);
        $stmtFoto->bindValue(":tipo", $tipo,    PDO::PARAM_INT);
        $stmtFoto->execute();
        $resultFoto = $stmtFoto->fetch(PDO::FETCH_ASSOC);

        $arquivo = $raizSite . "/img/nocapa.jpg";
        if ($resultFoto) {
            $pasta     = $resultFoto['pasta'];
            $foto      = $resultFoto['foto'];
            $diretorio = $raizSite . "/fotos/midias/" . $pasta;
            $arquivo   = $diretorio . "/" . $foto;
        }
    ?>


        <!-- Card da turma -->
        <div id="chaveRegistraturma"
            class="card-turma"
            data-id="<?= htmlspecialchars($tokem); ?>"
            style="background-image: url('<?= htmlspecialchars($arquivo); ?>');"
            data-aos="zoom-in">
            <div class="topo">
                <?= htmlspecialchars($value['nometurma'] ?? 'Turma'); ?>
            </div>
            <div class="rodape">
                <div class="data"><?= $linhaData; ?></div>
                <div class="abrir">
                    ABRIR <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>

    <?php } ?>

</div>