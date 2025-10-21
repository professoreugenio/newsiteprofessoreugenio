<?php
if (!isset($_SESSION['session_started_at'])) {
    $_SESSION['session_started_at'] = time();
} elseif ((time() - (int)$_SESSION['session_started_at']) > SESSION_TTL) {
    unset($_SESSION['nav'], $_SESSION['nav_set_at'], $_SESSION['af'], $_SESSION['af_set_at'], $_SESSION['ts'], $_SESSION['prg_redirect_done']);
    session_regenerate_id(true);
    $_SESSION['session_started_at'] = time();
}
/* ===== Util ===== */
function get_param(string $k): ?string
{
    if (!array_key_exists($k, $_GET)) return null;        // <- não enviado
    $v = trim((string)$_GET[$k]);
    if ($v === '') return null;                           // <- enviado porém vazio
    if (strlen($v) > 8192) $v = substr($v, 0, 8192);
    return $v;
}
/* ===== Captura condicional ===== */
$paramNav = get_param('nav');   // pode vir
$paramAf  = get_param('af');    // pode NÃO vir e tá tudo bem
$paramTs  = get_param('ts');    // opcional
// Persistir APENAS quando vier na URL (não sobrescreve sessão com vazio)
if ($paramNav !== null) {
    $_SESSION['nav'] = $paramNav;
    $_SESSION['nav_set_at'] = time();
}
if ($paramAf  !== null) {
    $_SESSION['af']  = $paramAf;
    $_SESSION['af_set_at']  = time();
}
if ($paramTs  !== null) {
    $_SESSION['ts']  = $paramTs;
}
// Exposição: GET > SESSION > '' (sem notices se 'af' não vier)
$nav = $_GET['nav'] ?? ($_SESSION['nav'] ?? '');
$af  = $_GET['af']  ?? ($_SESSION['af']  ?? '');
$ts  = $_GET['ts']  ?? ($_SESSION['ts']  ?? '');
/* ===== Redirect condicional (PRG) =====
   Redireciona só quando chegaram novos parâmetros (mesmo que 'af' não venha).
   Evita looping usando uma flag de “já redirecionado”.
   Debug: adicione ?noredir=1 para ficar na index.
*/
$hasNewParams   = ($nav !== null) || ($af !== null) || ($ts !== null);
$prgAlreadyDone = !empty($_SESSION['prg_redirect_done']);
$noredir        = isset($_GET['noredir']) && $_GET['noredir'] == '1';
if ($hasNewParams && !$prgAlreadyDone && !$noredir) {
    $_SESSION['prg_redirect_done'] = time();
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Location: ../');
        exit;
    }
}
// ==== 1) Validar a sessão/nav e decodificar com segurança ====
$navRaw = $_SESSION['nav'] ?? '';
if ($navRaw === '') {
    // Sem NAV na sessão — defina defaults e retorne cedo.
    $idCursoVenda = 0;
} else {
    try {
        $decNavCurso = encrypt($navRaw, 'd'); // pode lançar erro dependendo da sua função
    } catch (Throwable $e) {
        // Falha ao decodificar: faça log e siga com id 0
        // error_log('NAV decrypt error: ' . $e->getMessage());
        $decNavCurso = '';
    }
    // Tentar extrair $idCursoVenda
    $idCursoVenda = 0;
    if ($decNavCurso !== '') {
        /**
         * CENÁRIO A: se o nav decodificado foi algo como "a=foo&id=123&foto=456"
         *            usamos parse_str (mais robusto do que explode fixo).
         */
        parse_str($decNavCurso, $navParams);
        if (isset($navParams['id'])) {
            $idCursoVenda = (int)$navParams['id'];
        }
        /**
         * CENÁRIO B (fallback): manter compat. com explode("&") nas posições 1/2
         * decNavCurso = "qualquercoisa&123&456"
         */
        if ($idCursoVenda === 0) {
            $exp = explode('&', $decNavCurso);
            // posição 1 = id do curso, posição 2 = id da foto (de acordo com o seu padrão)
            $idCursoVenda = isset($exp[1]) ? (int)preg_replace('/\D+/', '', $exp[1]) : 0;
            $idFoto       = $exp[2] ?? null;
        }
    }
}
// Garantia de tipo
$idCursoVenda = max(0, (int)$idCursoVenda);
// ==== 2) Defaults para todas as variáveis (evita undefined posteriormente) ====
$idCurso = '';
$enIdCurso = '';
$nomeTurma = '';
$descricao = '';
$lead = '';
$chaveTurma = '';
$horamanha = '';
$horatarde = '';
$horanoite = '';
$vendaliberada = '';
$horasaulast = '';
$valorvenda = '';
$chavepix = '';
$chavepixvalorvenda = '';
$valoranual = '';
$valorvendavitalicia = '';
$chavepixvitalicia = '';
$linkpagseguro = '';
$linkpagsegurovitalicia = '';
$linkmercadopago = '';
$linkmercadopagovitalicio = '';
$valorhoraaula = '';
$imgqrcodecurso = '';
$imgqrcodeanual = '';
$imgqrcodevitalicio = '';
$imgMidiaCurso = 'https://professoreugenio.com/img/cat-2.jpg';
$nomeCurso = '';
$hero = '';
$sobreocurso = '';
$beneficios = '';
$cta = '';
$Codigochave = '';
// ==== 3) Se não houver curso id válido, encerre cedo com defaults ====
if ($idCursoVenda === 0) {
    // Nada a consultar — mantém defaults e encerra.
    return;
}
// ==== 4) Buscar TURMA (JOIN com chave) ====
$query = $con->prepare("
    SELECT 
        t.codcursost,
        t.nometurma,
        t.previa,
        t.lead,
        t.chave AS chave,
        t.horadem,
        t.horadet,
        t.horaden,
        t.nomeprofessor,
        t.visivelst,
        t.horasaulast,
        t.valorvenda,
        t.chavepix,
        t.chavepixvalorvenda,
        t.linkwhatsapp,
        t.valoranual,
        t.valorvendavitalicia,
        t.chavepixvitalicia,
        t.linkpagseguro,
        t.linkpagsegurovitalicia,
        t.linkmercadopago,
        t.linkmercadopagovitalicio,
        t.valorhoraaula,
        t.imgqrcodecurso,
        t.imgqrcodeanual,
        t.imgqrcodevitalicio
    FROM new_sistema_cursos_turmas AS t
    INNER JOIN new_sistema_chave AS c ON c.chaveturmasc = t.chave
    WHERE t.codcursost = :id AND t.comercialt = '1'
    LIMIT 1
");
$query->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
$query->execute();
$rwTurma = $query->fetch(PDO::FETCH_ASSOC);
if ($rwTurma) {
    // Variáveis principais
    $idCurso      = (string)($rwTurma['codcursost'] ?? '');
    $enIdCurso    = $idCurso !== '' ? encrypt($idCurso, 'e') : '';
    $nomeTurma    = $rwTurma['nometurma'] ?? '';
    $descricao    = $rwTurma['previa'] ?? '';
    $lead         = $rwTurma['lead'] ?? '';
    $chaveTurma   = $rwTurma['chave'] ?? '';
    // horários
    $horamanha    = $rwTurma['horadem'] ?? '';
    $horatarde    = $rwTurma['horadet'] ?? '';
    $horanoite    = $rwTurma['horaden'] ?? '';
    // comercial
    $vendaliberada          = $rwTurma['visivelst'] ?? '';
    $horasaulast            = $rwTurma['horasaulast'] ?? '';
    $valorvenda             = $rwTurma['valorvenda'] ?? '';
    $chavepix               = $rwTurma['chavepix'] ?? '';
    $chavepixvalorvenda     = $rwTurma['chavepixvalorvenda'] ?? '';
    $valoranual             = $rwTurma['valoranual'] ?? '';
    $valorvendavitalicia    = $rwTurma['valorvendavitalicia'] ?? '';
    $chavepixvitalicia      = $rwTurma['chavepixvitalicia'] ?? '';
    $linkpagseguro          = $rwTurma['linkpagseguro'] ?? '';
    $linkwhatsapp          = $rwTurma['linkwhatsapp'] ?? '';
    $linkpagsegurovitalicia = $rwTurma['linkpagsegurovitalicia'] ?? '';
    $linkmercadopago        = $rwTurma['linkmercadopago'] ?? '';
    $linkmercadopagovitalicio = $rwTurma['linkmercadopagovitalicio'] ?? '';
    $valorhoraaula          = $rwTurma['valorhoraaula'] ?? '';
    $imgqrcodecurso         = $rwTurma['imgqrcodecurso'] ?? '';
    $imgqrcodeanual         = $rwTurma['imgqrcodeanual'] ?? '';
    $imgqrcodevitalicio     = $rwTurma['imgqrcodevitalicio'] ?? '';
}
// ==== 5) Buscar FOTO do curso (midias) ====
$tipo = 1;
$query = $con->prepare("
    SELECT 
        f.pasta AS pasta,
        f.foto  AS foto
    FROM new_sistema_cursos AS categorias
    INNER JOIN new_sistema_midias_fotos_PJA AS f
        ON categorias.pasta = f.pasta
    WHERE f.codpublicacao = :id AND f.tipo = :tipo
    LIMIT 1
");
$query->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
$query->bindValue(':tipo', $tipo, PDO::PARAM_INT);
$query->execute();
$rwFotoCurso = $query->fetch(PDO::FETCH_ASSOC);
if ($rwFotoCurso && !empty($rwFotoCurso['pasta']) && !empty($rwFotoCurso['foto'])) {
    $pastaMidia = $rwFotoCurso['pasta'];
    $fotoMidia  = $rwFotoCurso['foto'];
    $imgMidiaCurso = "https://professoreugenio.com/fotos/midias/{$pastaMidia}/{$fotoMidia}";
}
// ==== 6) Buscar DADOS do curso (nome/hero/sobre/beneficios/cta) ====
$queryCurso = $con->prepare("
    SELECT 
        nomecurso,
        heroSC,
        sobreSC,
        beneficiosSC,
        ctaSC
    FROM new_sistema_cursos
    WHERE codigocursos = :id
    LIMIT 1
");
$queryCurso->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
$queryCurso->execute();
$rwCurso = $queryCurso->fetch(PDO::FETCH_ASSOC);
if ($rwCurso) {
    $nomeCurso   = $rwCurso['nomecurso'] ?? '';
    $hero        = $rwCurso['heroSC'] ?? '';
    $sobreocurso = $rwCurso['sobreSC'] ?? '';
    $beneficios  = $rwCurso['beneficiosSC'] ?? '';
    $cta         = $rwCurso['ctaSC'] ?? '';
    $youtubeurl         = $rwCurso['youtubeurl'] ?? '';
    parse_str(parse_url($youtubeurl, PHP_URL_QUERY), $params);
    // Obter o valor do parâmetro "v"
    $videoyoutube = $params['v'] ?? ''.  $hero.'';
}
// ==== 7) Buscar CÓDIGO da chave (se houver chaveTurma) ====
if (!empty($chaveTurma)) {
    $querychave = $con->prepare("
        SELECT chavesc
        FROM new_sistema_chave
        WHERE chaveturmasc = :campo
        LIMIT 1
    ");
    $querychave->bindValue(':campo', $chaveTurma, PDO::PARAM_STR);
    $querychave->execute();
    $rwChave = $querychave->fetch(PDO::FETCH_ASSOC);
    if ($rwChave && !empty($rwChave['chavesc'])) {
        $Codigochave = encrypt($rwChave['chavesc'], 'e');
    }
}
