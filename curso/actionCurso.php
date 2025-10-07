<?php
// Blindagem contra "headers already sent"
if (!headers_sent()) {
    ob_start();
}

define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php';

/**
 * Roteador por variável:
 * - Lê a variável de entrada (?Atvs, ?Atv, ?lc, ?mdl, ?cls, ?var, ?pubult&?pub)
 * - Monta o NAV conforme a rota
 * - Grava cookie e carrega a página correspondente
 */

date_default_timezone_set('America/Fortaleza');
$DATA = date('Y-m-d');
$HORA = date('H:i:s');
$TS   = time();

$COOKIE_HORAS = 6;
$EXPIRES = $TS + ($COOKIE_HORAS * 3600);

// Chave única que você usa na query string
$CHAVE = function_exists('gerachave') ? gerachave() : uniqid('', true);

// ===== Helpers =====
function dec(?string $v): ?string
{
    if ($v === null || $v === '') return null;
    $d = encrypt($v, 'd');
    return (is_string($d) && $d !== '') ? $d : null;
}
function enc(string $v): string
{
    return encrypt($v, 'e');
}

function getExpNav(): array
{
    if (empty($_COOKIE['nav'])) return [];
    $d = encrypt($_COOKIE['nav'], 'd');
    if (!is_string($d) || $d === '') return [];
    return explode('&', $d);
}

function setCookieNav(string $navStr, int $expires): void
{
    $value = enc($navStr);
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    if (PHP_VERSION_ID >= 70300) {
        setcookie('nav', $value, [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie('nav', $value, $expires, '/', '', $secure, true);
    }
}

function absolute_url(string $relative): string
{
    // Gera URL absoluta para ambientes atrás de proxy/CDN mais rígidos
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $rel    = ltrim($relative, '/');
    return $scheme . '://' . $host . '/' . $rel;
}

function go(string $url): void
{
    // Anti-cache
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    if (!headers_sent()) {
        if (ob_get_length()) {
            @ob_end_clean();
        }
        header('Location: ' . $url, true, 302);
        exit;
    }
    // Fallback (caso algum include tenha cuspido saída)
    $esc = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><meta charset="utf-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . $esc . '">';
    echo '<script>location.replace(' . json_encode($url) . ')</script>';
    echo '<a href="' . $esc . '">Continuar</a>';
    exit;
}

/**
 * Atualiza NAV (array com 8 posições) e redireciona para a página alvo.
 * $navArr esperado: [idaluno, idcurso, idturma, idmodulo, idpublic/idaula, v6, v7, v8]
 */
function setNavAndGo(array $navArr, string $dest, int $expires, string $chave): void
{
    $navArr = array_pad($navArr, 8, '0');
    $nova = implode('&', $navArr);
    setCookieNav($nova, $expires);

    // Pode usar absoluta se preferir:
    $url = $dest . '?' . $chave;
    go($url);
}

// ===== Estado atual (nav existente) =====
$expnav = getExpNav(); // [0=>idaluno,1=>idcurso,2=>idturma,3=>idmodulo,4..7 extra]

// ====== ROTA: ?Atvs ======
// Carrega questionário => busca idpublic e vai para modulo_ViewAtividade.php
if (!empty($_GET['Atvs'])) {
    if (count($expnav) >= 4) {
        $idQuestionario = dec($_GET['Atvs']);
        if ($idQuestionario) {
            $stmt = $con->prepare("SELECT idpublicacaocq FROM a_curso_questionario WHERE codigoquestionario = :id");
            $stmt->bindValue(':id', $idQuestionario, PDO::PARAM_INT);
            $stmt->execute();
            $rw = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($rw['idpublicacaocq'])) {
                $idpublic = (string)$rw['idpublicacaocq'];
                $nav = [$expnav[0], $expnav[1], $expnav[2], $expnav[3], $idpublic, '0', '0', '0'];
                setNavAndGo($nav, 'modulo_ViewAtividade.php', $EXPIRES, $CHAVE);
            }
        }
    }
}

// ====== ROTA: ?Atv ======
// Define código de atividade e vai para modulo_ViewAtividade.php
if (!empty($_GET['Atv'])) {
    if (count($expnav) >= 4) {
        $idAtv = dec($_GET['Atv']);
        if ($idAtv) {
            $nav = [$expnav[0], $expnav[1], $expnav[2], $expnav[3], ($expnav[4] ?? '0'), $idAtv, '0', '0'];
            setNavAndGo($nav, 'modulo_ViewAtividade.php', $EXPIRES, $CHAVE);
        }
    }
}

// ====== ROTA: ?lc ======
// Abre lição (registra andamento) e vai para modulo_licao.php
if (!empty($_GET['lc'])) {
    if (count($expnav) >= 4) {
        $codigolicao = dec($_GET['lc']);
        if ($codigolicao) {
            // Registrar/atualizar andamento
            $q = $con->prepare("SELECT 1 FROM a_aluno_andamento_aula 
                                WHERE idpublicaa = :idaula AND idalunoaa = :idaluno AND idmoduloaa = :idmodulo");
            $q->execute([':idaula' => $codigolicao, ':idaluno' => $expnav[0], ':idmodulo' => $expnav[3]]);
            if (!$q->fetchColumn()) {
                $ins = $con->prepare("INSERT INTO a_aluno_andamento_aula
                    (idalunoaa, idpublicaa, idcursoaa, idturmaaa, idmoduloaa, dataaa, horaaa)
                    VALUES (:idalunoaa,:idpublicaa,:idcursoaa,:idturmaaa,:idmoduloaa,:dataaa,:horaaa)");
                $ins->execute([
                    ':idalunoaa' => $expnav[0],
                    ':idpublicaa' => $codigolicao,
                    ':idcursoaa' => $expnav[1],
                    ':idturmaaa' => $expnav[2],
                    ':idmoduloaa' => $expnav[3],
                    ':dataaa' => $DATA,
                    ':horaaa' => $HORA
                ]);
            } else {
                $upd = $con->prepare("UPDATE a_aluno_andamento_aula
                    SET dataaa=:dataaa, horaaa=:horaaa
                    WHERE idalunoaa=:idalunoaa AND idpublicaa=:idpublicaa AND idmoduloaa=:idmoduloaa");
                $upd->execute([
                    ':dataaa' => $DATA,
                    ':horaaa' => $HORA,
                    ':idalunoaa' => $expnav[0],
                    ':idpublicaa' => $codigolicao,
                    ':idmoduloaa' => $expnav[3]
                ]);
            }

            $nav = [$expnav[0], $expnav[1], $expnav[2], $expnav[3], $codigolicao, '0', '0', '0'];
            setNavAndGo($nav, 'modulo_licao.php', $EXPIRES, $CHAVE);
        }
    }
}

// ====== ROTA: ?mdl ======
// Troca de módulo e vai para modulo_status.php
if (!empty($_GET['mdl'])) {
    $decmdl = dec($_GET['mdl']);
    if ($decmdl) {
        $e = array_pad(explode('&', $decmdl), 4, '0'); // [idaluno,idcurso,idturma,idmodulo]
        $nav = [$e[0], $e[1], $e[2], $e[3], '0', '0', '0', '0'];
        setNavAndGo($nav, 'modulo_status.php', $EXPIRES, $CHAVE);
    }
}

// ====== ROTA: ?cls ======
// Volta para curso.php (marca segunda posição extra como 1, conforme sua regra)
if (!empty($_GET['cls'])) {
    if (count($expnav) >= 4) {
        $nav = [$expnav[0], $expnav[1], $expnav[2], $expnav[3], '0', '1', '0', '0'];
        setNavAndGo($nav, 'curso.php', $EXPIRES, $CHAVE);
    }
}

// ====== ROTA: ?var ======
// Recria nav reduzido e volta para curso.php com ts
if (!empty($_GET['var'])) {
    $decvar = dec($_GET['var']);
    if ($decvar) {
        $e = array_pad(explode('&', $decvar), 2, '0'); // [idaluno,idcurso]
        $nova = $e[0] . '&' . $e[1] . '&0&0&0';        // seu padrão reduzido
        setCookieNav($nova, $EXPIRES);
        go('curso.php?var=' . enc($nova) . '&ts=' . $TS);
    }
}

// ====== ROTA: ?pubult + ?pub ======
// Abre última publicação do módulo (registra andamento) e vai para modulo_licao.php
// ====== ROTA: ?pubult + ?pub ======
// Abre última publicação do módulo (registra andamento) e vai para modulo_licao.php
// ====== ROTA: ?pubult + ?pub ======
// Abre publicação do módulo (registra andamento) e vai para modulo_licao.php
if (!empty($_GET['pubult'])) {

    $nav = $_GET['pubult'];
    $dec = encrypt($nav, $action = 'd');
    $exp = explode('&', $dec);
    $idaluno = $exp[0];
    $idcurso = $exp[1];
    $idturma = $exp[2];
    $decmdl = $exp[3];
    $decpub = $exp[4];

    $nav = [$idaluno, $idcurso, $idturma, $decmdl, $decpub, '1', '0', '0'];
    setCookieNav(implode('&', $nav), $EXPIRES);

    // IMPORTANTE: redirecionar para forçar nova requisição (cookie visível)
    go('modulo_licao.php?' . $CHAVE);
}



// ===== Fallback: nenhum parâmetro reconhecido =====
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Navegação</title>
    <meta http-equiv="refresh" content="10; url=curso.php?<?= htmlspecialchars($CHAVE) ?>">
    <style>
        body {
            background: #001428;
            color: #fff;
            font-family: system-ui, Arial;
            padding: 24px
        }
    </style>
</head>

<body>
    <h1>Navegação</h1>
    <p>Nenhuma variável reconhecida. Redirecionando…</p>
    <p><a href="curso.php?<?= htmlspecialchars($CHAVE) ?>" style="color:#9cf">Ir agora</a></p>
    <script>
        location.href = 'curso.php?<?= addslashes($CHAVE) ?>'
    </script>
    <script src="acessosv1.0/ajax_registraAcesso.js" defer></script>
</body>

</html>