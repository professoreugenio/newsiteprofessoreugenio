<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1)); // raiz do site (ex.: /public_html)

/* ===================== INCLUDES ===================== */
require_once APP_ROOT . '/conexao/class.conexao.php';   // $con = config::connect();
require_once APP_ROOT . '/autenticacao.php';

/* ===================== SESSÃO (4H) ===================== */
const SESSION_TTL = 4 * 3600;

session_set_cookie_params([
    'lifetime' => SESSION_TTL,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['session_started_at'])) {
    $_SESSION['session_started_at'] = time();
} elseif ((time() - (int)$_SESSION['session_started_at']) > SESSION_TTL) {
    unset($_SESSION['nav'], $_SESSION['nav_set_at'], $_SESSION['af'], $_SESSION['af_set_at'], $_SESSION['ts'], $_SESSION['prg_redirect_done']);
    session_regenerate_id(true);
    $_SESSION['session_started_at'] = time();
}

/* ===================== Helpers ===================== */
function get_param(string $k): ?string
{
    if (!array_key_exists($k, $_GET)) return null;
    $v = trim((string)$_GET[$k]);
    if ($v === '') return null;
    if (strlen($v) > 8192) $v = substr($v, 0, 8192);
    return $v;
}

/* ===================== CAPTURA GET -> SESSÃO ===================== */
$paramNav = get_param('nav');   // pode vir
$paramAf  = get_param('af');    // pode não vir
$paramTs  = get_param('ts');    // opcional

if ($paramNav !== null) {
    $_SESSION['nav']        = $paramNav;
    $_SESSION['nav_set_at'] = time();
}
if ($paramAf !== null) {
    $_SESSION['af']        = $paramAf;
    $_SESSION['af_set_at'] = time();
}
if ($paramTs !== null) {
    $_SESSION['ts'] = $paramTs;
}

/* Exposição (sem forçar "novos"): GET > SESSION > '' */
$nav = $_GET['nav'] ?? ($_SESSION['nav'] ?? '');
$af  = $_GET['af']  ?? ($_SESSION['af']  ?? '');
$ts  = $_GET['ts']  ?? ($_SESSION['ts']  ?? '');

/* ===================== PRG (Post/Redirect/Get-like) ===================== */
/**
 * Redireciona APENAS quando chegaram NOVOS parâmetros pela URL.
 * Evita loop usando uma flag de sessão.
 * Redireciona para a MESMA rota, porém sem querystring (limpa a URL e mantém sessão).
 * Debug: ?noredir=1 para não redirecionar.
 */
$hasNewParams   = ($paramNav !== null) || ($paramAf !== null) || ($paramTs !== null);
$prgAlreadyDone = !empty($_SESSION['prg_redirect_done']);
$noredir        = isset($_GET['noredir']) && $_GET['noredir'] == '1';

if ($hasNewParams && !$prgAlreadyDone && !$noredir) {
    $_SESSION['prg_redirect_done'] = time();

    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        // Redireciona para ESTA MESMA PÁGINA sem querystring
        $self = strtok($_SERVER['REQUEST_URI'], '?'); // ex.: /vendassite/index.php
        header('Location: ' . $self);
        exit;
    }
}

/* ===================== DECODIFICA NAV -> idCursoVenda ===================== */
$idCursoVenda = 0;
$navRaw       = $_SESSION['nav'] ?? '';
if ($navRaw !== '') {
    try {
        $decNavCurso = encrypt($navRaw, 'd'); // sua função (d = decrypt)
    } catch (Throwable $e) {
        $decNavCurso = '';
        // error_log('NAV decrypt error: ' . $e->getMessage());
    }

    if ($decNavCurso !== '') {
        // Tenta padrão "a=...&id=123&foto=456"
        parse_str($decNavCurso, $navParams);
        if (isset($navParams['id'])) {
            $idCursoVenda = (int)$navParams['id'];
        }
        // Fallback "qualquer&123&456"
        if ($idCursoVenda === 0) {
            $exp = explode('&', $decNavCurso);
            $idCursoVenda = isset($exp[1]) ? (int)preg_replace('/\D+/', '', $exp[1]) : 0;
            $idFoto       = $exp[2] ?? null;
        }
    }
}
$idCursoVenda = max(0, (int)$idCursoVenda);

/* ===================== DEFAULTS ===================== */
$idCurso = $enIdCurso = $nomeTurma = $descricao = $lead = $chaveTurma = '';
$aovivo = 0;
$horamanha = $horatarde = $horanoite = '';
$vendaliberada = $horasaulast = $valorvenda = '';
$chavepix = $chavepixvalorvenda = '';
$valorbruto = $valoravista = '';
$valorbruto = $valoravista = '';
$chavepixvitalicia = '';
$linkpagseguro = $linkpagsegurovitalicia = '';
$linkmercadopago = $linkmercadopagovitalicio = '';
$valorhoraaula = '';
$imgqrcodecurso = $imgqrcodeanual = $imgqrcodevitalicio = '';
$imgMidiaCurso = 'https://professoreugenio.com/img/cat-2.jpg';
$nomeCurso = $hero = $sobreocurso = $beneficios = $cta = '';
$Codigochave = '';
$youtubeurl = ''; // id do vídeo

/* ===================== BUSCAS (se houver idCursoVenda) ===================== */
if ($idCursoVenda > 0) {
    // TURMA
    $q1 = $con->prepare("
        SELECT 
            t.codcursost,
            t.nometurma,
            t.aovivoct,
            t.previa,
            t.lead,
            t.chave AS chave,
            t.horadem, t.horadet, t.horaden,
            t.visivelst, t.horasaulast, t.valorvenda,
            t.chavepix, t.chavepixvalorvenda,
            t.valoranual, t.valorvendavitalicia,
            t.chavepixvitalicia,
            t.linkpagseguro, t.linkpagsegurovitalicia,
            t.linkmercadopago, t.linkmercadopagovitalicio,
            t.valorhoraaula,
            t.imgqrcodecurso, t.imgqrcodeanual, t.imgqrcodevitalicio,
            t.linkwhatsapp,
            t.celularprofessorct
        FROM new_sistema_cursos_turmas AS t
        INNER JOIN new_sistema_chave AS c ON c.chaveturmasc = t.chave
        WHERE t.codcursost = :id AND t.comercialt = '1'
        LIMIT 1
    ");
    $q1->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
    $q1->execute();
    $rwTurma = $q1->fetch(PDO::FETCH_ASSOC);

    if ($rwTurma) {
        $idCurso      = (string)($rwTurma['codcursost'] ?? '');
        $enIdCurso    = $idCurso !== '' ? encrypt($idCurso, 'e') : '';
        $nomeTurma    = $rwTurma['nometurma'] ?? '';
        $descricao    = $rwTurma['previa'] ?? '';
        $lead         = $rwTurma['lead'] ?? '';
        $chaveTurma   = $rwTurma['chave'] ?? '';
        $aovivo       = (int)($rwTurma['aovivoct'] ?? 0);

        $horamanha    = $rwTurma['horadem'] ?? '';
        $horatarde    = $rwTurma['horadet'] ?? '';
        $horanoite    = $rwTurma['horaden'] ?? '';

        $vendaliberada          = $rwTurma['visivelst'] ?? '';
        $horasaulast            = $rwTurma['horasaulast'] ?? '';
        $valorvenda             = $rwTurma['valorvenda'] ?? '';
        $chavepix               = $rwTurma['chavepix'] ?? '';
        $chavepixvalorvenda     = $rwTurma['chavepixvalorvenda'] ?? '';
        $valorbruto             = $rwTurma['valorbruto'] ?? '';
        $valoravista    = $rwTurma['valoravistact'] ?? '';
        $chavepixvitalicia      = $rwTurma['chavepixvitalicia'] ?? '';
        $linkpagseguro          = $rwTurma['linkpagseguro'] ?? '';
        $linkpagsegurovitalicia = $rwTurma['linkpagsegurovitalicia'] ?? '';
        $linkmercadopago        = $rwTurma['linkmercadopago'] ?? '';
        $linkmercadopagovitalicio = $rwTurma['linkmercadopagovitalicio'] ?? '';
        $valorhoraaula          = $rwTurma['valorhoraaula'] ?? '';
        $imgqrcodecurso         = $rwTurma['imgqrcodecurso'] ?? '';
        $imgqrcodeanual         = $rwTurma['imgqrcodeanual'] ?? '';
        $imgqrcodevitalicio     = $rwTurma['imgqrcodevitalicio'] ?? '';

        // WhatsApp: sanitiza celular ou usa fallback
        $cel = preg_replace('/\D+/', '', (string)($rwTurma['celularprofessorct'] ?? ''));
        if ($cel === '') $cel = '5585995637577';
        if (strpos($cel, '55') !== 0) $cel = '55' . $cel;
        $linkwhatsapp = 'https://wa.me/' . $cel . '?text=' . rawurlencode('Gostaria de mais informações sobre o curso');
    } else {
        // fallback de WhatsApp
        $linkwhatsapp = 'https://wa.me/5585995637577?text=' . rawurlencode('Gostaria de mais informações sobre o curso');
    }

    // FOTO
    $q2 = $con->prepare("
        SELECT f.pasta, f.foto
        FROM new_sistema_cursos AS categorias
        INNER JOIN new_sistema_midias_fotos_PJA AS f ON categorias.pasta = f.pasta
        WHERE f.codpublicacao = :id AND f.tipo = :tipo
        LIMIT 1
    ");
    $q2->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
    $q2->bindValue(':tipo', 1, PDO::PARAM_INT);
    $q2->execute();
    $rwFoto = $q2->fetch(PDO::FETCH_ASSOC);
    if ($rwFoto && !empty($rwFoto['pasta']) && !empty($rwFoto['foto'])) {
        $imgMidiaCurso = "https://professoreugenio.com/fotos/midias/{$rwFoto['pasta']}/{$rwFoto['foto']}";
    }

    // CURSO
    $q3 = $con->prepare("
        SELECT nomecurso, youtubeurl, heroSC, sobreSC, beneficiosSC, ctaSC
        FROM new_sistema_cursos
        WHERE codigocursos = :id
        LIMIT 1
    ");
    $q3->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
    $q3->execute();
    $rwCurso = $q3->fetch(PDO::FETCH_ASSOC);
    if ($rwCurso) {
        $nomeCurso   = $rwCurso['nomecurso'] ?? '';
        $youtube     = $rwCurso['youtubeurl'] ?? '';
        $hero        = $rwCurso['heroSC'] ?? '';
        $sobreocurso = $rwCurso['sobreSC'] ?? '';
        $beneficios  = $rwCurso['beneficiosSC'] ?? '';
        $cta         = $rwCurso['ctaSC'] ?? '';

        // Extrai ID do YouTube (aceita várias formas)
        $youtube = trim($youtube);
        $youtubeurl = '';
        if ($youtube !== '') {
            // já é um ID (11 chars alfanum/hífen/_)? usa direto
            if (preg_match('~^[a-zA-Z0-9_-]{11}$~', $youtube)) {
                $youtubeurl = $youtube;
            } else {
                $host = parse_url($youtube, PHP_URL_HOST) ?: '';
                if (strpos($host, 'youtu.be') !== false) {
                    $path = ltrim((string)parse_url($youtube, PHP_URL_PATH), '/');
                    if (preg_match('~^[a-zA-Z0-9_-]{11}$~', $path)) $youtubeurl = $path;
                } else {
                    parse_str((string)parse_url($youtube, PHP_URL_QUERY), $p);
                    if (!empty($p['v']) && preg_match('~^[a-zA-Z0-9_-]{11}$~', $p['v'])) {
                        $youtubeurl = $p['v'];
                    } else {
                        // /embed/<id>
                        $path = (string)parse_url($youtube, PHP_URL_PATH);
                        if (preg_match('~/embed/([a-zA-Z0-9_-]{11})~', $path, $m)) {
                            $youtubeurl = $m[1];
                        }
                    }
                }
            }
        }
    }

    // CHAVE (para próximas etapas)
    if (!empty($chaveTurma)) {
        $q4 = $con->prepare("
            SELECT chavesc
            FROM new_sistema_chave
            WHERE chaveturmasc = :campo
            LIMIT 1
        ");
        $q4->bindValue(':campo', $chaveTurma, PDO::PARAM_STR);
        $q4->execute();
        $rwChave = $q4->fetch(PDO::FETCH_ASSOC);
        if ($rwChave && !empty($rwChave['chavesc'])) {
            $Codigochave = encrypt($rwChave['chavesc'], 'e');
        }
    }
}

/* ===================== Destaques (chips) ===================== */
function fmtHora(?string $h): ?string
{
    if (!$h || $h === '00:00:00') return null;
    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $h) === 1) return substr($h, 0, 5);
    return $h;
}
$chips = [];
if ((int)$aovivo === 1) $chips[] = ['icon' => 'bi-broadcast',   'label' => 'ONLINE AO VIVO', 'class' => 'badge-live'];
if ($m = fmtHora($horamanha)) $chips[] = ['icon' => 'bi-sunrise',    'label' => 'MANHÃ às ' . $m, 'class' => 'badge-time'];
if ($t = fmtHora($horatarde)) $chips[] = ['icon' => 'bi-sunset',     'label' => 'TARDE às ' . $t, 'class' => 'badge-time'];
if ($n = fmtHora($horanoite)) $chips[] = ['icon' => 'bi-moon-stars', 'label' => 'NOITE às ' . $n, 'class' => 'badge-time'];

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Metas -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Curso <?= htmlspecialchars($nomeCurso ?: 'Excel para Concursos', ENT_QUOTES) ?> — Professor Eugênio</title>
    <meta name="description" content="Domine Excel para gabaritar questões de concursos: funções, gráficos, tabelas, atalhos e simulados. Aulas online, material para download e certificação.">
    <link rel="canonical" href="https://professoreugenio.com/curso-excel-concursos">

    <!-- OG/Twitter -->
    <meta property="og:title" content="<?= htmlspecialchars($nomeCurso ?: 'Excel para Concursos', ENT_QUOTES) ?> — Professor Eugênio">
    <meta property="og:description" content="Domine Excel para gabaritar questões de concursos. Aulas online, simulados e material para download.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= htmlspecialchars($imgMidiaCurso, ENT_QUOTES) ?>">
    <meta property="og:url" content="https://professoreugenio.com/curso-excel-concursos">
    <meta name="twitter:card" content="summary_large_image">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://professoreugenio.com/vendassite/vendasv1.0/CSS_vendas.css" rel="stylesheet">
</head>

<body>

    <!-- ===================== NAV ===================== -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">
                <i class="bi bi-microsoft me-1"></i> Professor Eugênio
            </a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="#hero">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beneficios">Benefícios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="#grade">Grade</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-cta" href="#cta"><i class="bi bi-lightning-charge-fill me-1"></i> Inscreva-se</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== HERO ===================== -->
    <section id="hero" class="hero pt-5">
        <div class="container position-relative">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-trophy me-1"></i> Curso de <?= htmlspecialchars($nomeCurso ?: 'Excel para Concursos', ENT_QUOTES) ?>
                    </span>

                    <?= $hero ?: '<h1 class="display-5 fw-bold mb-3">Excel para Concursos</h1><p class="lead">A preparação direta ao ponto para dominar o Excel nas provas.</p>' ?>

                    <?php if (!empty($chips)): ?>
                        <div class="chip-line mt-4 mb-4" data-aos="fade-up" data-aos-delay="50">
                            <?php foreach ($chips as $c): ?>
                                <span class="badge <?= htmlspecialchars($c['class'], ENT_QUOTES) ?>">
                                    <i class="bi <?= htmlspecialchars($c['icon'], ENT_QUOTES) ?>"></i>
                                    <?= htmlspecialchars($c['label'], ENT_QUOTES) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="#cta" class="btn btn-cta btn-lg"><i class="bi bi-star-fill me-2"></i> Garantir minha vaga</a>
                        <a href="#grade" class="btn btn-outline-soft btn-lg"><i class="bi bi-journal-check me-2"></i> Ver a grade</a>
                    </div>

                    <div class="d-flex gap-3 mt-4 small text-white-50">
                        <div><i class="bi bi-camera-video me-1"></i> Aulas ao vivo + gravadas</div>
                        <div><i class="bi bi-patch-check me-1"></i> Certificação</div>
                        <div><i class="bi bi-file-earmark-arrow-down me-1"></i> PDFs e simulados</div>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-left">
                    <div class="hero-card p-3 p-md-4">
                        <div style="background-color:#ffa500;color:#fff;font-weight:bold;padding:2px 16px;border-radius:8px;display:inline-block;">
                            Aula gratuita
                        </div>
                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden border border-1 border-light mt-2">
                            <iframe
                                src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeurl ?: 'dQw4w9WgXcQ', ENT_QUOTES) ?>"
                                title="Apresentação do Curso"
                                allowfullscreen loading="lazy"></iframe>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="icon-badge"><i class="bi bi-clock fs-4"></i></div>
                            <div class="small">Início imediato • Acesso ao conteúdo gravado • Suporte direto com o professor</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== BENEFÍCIOS ===================== -->
    <section id="beneficios">
        <?= $beneficios ?: '<div class="container"><div class="card-dark p-4">Benefícios em breve.</div></div>' ?>
    </section>

    <!-- ===================== SOBRE ===================== -->
    <section id="sobre">
        <?= $sobreocurso ?: '<div class="container"><div class="card-dark p-4">Sobre o curso em breve.</div></div>' ?>
    </section>

    <!-- ===================== GRADE ===================== -->
    <section id="grade">
        <div class="container">
            <div class="text-center mb-4" data-aos="fade-up">
                <div class="heading-2">Grade do Curso</div>
                <p class="lead lead-muted mb-0">Conteúdo organizado por módulos. Expanda cada módulo para ver as aulas.</p>
            </div>

            <?php
            $idcv = (int)$idCursoVenda;
            if ($idcv > 0) {
                if (!function_exists('h')) {
                    function h(string $s): string
                    {
                        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
                    }
                }
                $sql = "
                SELECT
                  m.codigomodulos AS mod_id,
                  m.codcursos     AS mod_curso,
                  m.modulo        AS mod_ordem,
                  m.nomemodulo    AS mod_nome,
                  m.visivelm      AS mod_visivel,
                  pc.idpublicacaopc AS pub_id,
                  pc.ordempc        AS pub_ordem,
                  pc.visivelpc      AS pub_visivel,
                  p.titulo          AS pub_titulo
                FROM new_sistema_modulos_PJA m
                LEFT JOIN a_aluno_publicacoes_cursos pc
                       ON pc.idmodulopc = m.codigomodulos
                      AND (pc.visivelpc IS NULL OR pc.visivelpc = 1)
                LEFT JOIN new_sistema_publicacoes_PJA p
                       ON p.codigopublicacoes = pc.idpublicacaopc
                WHERE m.codcursos = :curso AND m.visivelm = '1'
                ORDER BY
                  CASE WHEN m.modulo REGEXP '^[0-9]+$' THEN CAST(m.modulo AS UNSIGNED) ELSE 999999 END,
                  m.modulo,
                  CASE WHEN pc.ordempc IS NULL THEN 999999 ELSE pc.ordempc END,
                  p.titulo
            ";
                $stmt = $con->prepare($sql);
                $stmt->bindValue(':curso', $idcv, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $modules = [];
                foreach ($rows as $r) {
                    $mid = (int)$r['mod_id'];
                    if (!isset($modules[$mid])) {
                        $modules[$mid] = [
                            'id' => $mid,
                            'ordem' => (string)($r['mod_ordem'] ?? ''),
                            'nome' => (string)($r['mod_nome'] ?? ''),
                            'items' => []
                        ];
                    }
                    if (!empty($r['pub_id']) && !empty($r['pub_titulo'])) {
                        $modules[$mid]['items'][] = [
                            'id'    => (int)$r['pub_id'],
                            'ordem' => $r['pub_ordem'] !== null ? (int)$r['pub_ordem'] : 999999,
                            'title' => (string)$r['pub_titulo'],
                        ];
                    }
                }

                if (empty($modules)) {
                    echo '<div class="card-dark p-4"><div class="small text-white-50 mb-0">Conteúdo em atualização. Volte em breve.</div></div>';
                } else {
                    uasort($modules, function ($a, $b) {
                        $ai = ctype_digit((string)$a['ordem']) ? (int)$a['ordem'] : PHP_INT_MAX;
                        $bi = ctype_digit((string)$b['ordem']) ? (int)$b['ordem'] : PHP_INT_MAX;
                        if ($ai === $bi) return strnatcasecmp((string)$a['ordem'], (string)$b['ordem']);
                        return $ai <=> $bi;
                    });
                    echo '<div class="accordion mod-acc" id="accGrade">';
                    $idx = 0;
                    $delayStep = 50;
                    foreach ($modules as $mod) {
                        $idx++;
                        $collapseId = 'm' . $mod['id'];
                        $isFirst    = ($idx === 1);
                        $showClass  = $isFirst ? ' show' : '';
                        $collapsed  = $isFirst ? '' : ' collapsed';
                        $aosDelay   = ($idx - 1) * $delayStep;
                        $tituloMod  = 'Módulo ' . h((string)$mod['ordem']) . ' — ' . h($mod['nome']);
                        echo '<div class="accordion-item card-dark mb-3" data-aos="fade-up"' . ($aosDelay ? ' data-aos-delay="' . $aosDelay . '"' : '') . '>';
                        echo '  <h2 class="accordion-header">';
                        echo '    <button class="accordion-button fw-semibold' . $collapsed . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . h($collapseId) . '">' . $tituloMod . '</button>';
                        echo '  </h2>';
                        echo '  <div id="' . h($collapseId) . '" class="accordion-collapse collapse' . $showClass . '" data-bs-parent="#accGrade">';
                        echo '    <div class="accordion-body">';
                        if (!empty($mod['items'])) {
                            echo '      <ul class="mb-0 small">';
                            foreach ($mod['items'] as $item) {
                                echo '        <li>' . h($item['title']) . '</li>';
                            }
                            echo '      </ul>';
                        } else {
                            echo '      <div class="small text-white-50">Conteúdo deste módulo será liberado em breve.</div>';
                        }
                        echo '    </div>';
                        echo '  </div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<div class="card-dark p-4"><div class="small text-white-50 mb-0">Curso não identificado. Verifique o link de acesso.</div></div>';
            }
            ?>

            <div class="row g-4 mt-1">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-download me-2"></i>Materiais</div>
                        <p class="small text-white-50 mb-0">Planilhas-modelo e PDFs para reforço e prática dirigida.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="50">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-people me-2"></i>Comunidade</div>
                        <p class="small text-white-50 mb-0">Grupo de suporte e tira-dúvidas com o professor.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-award me-2"></i>Certificação</div>
                        <p class="small text-white-50 mb-0">Certificado digital ao final do curso.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-4" data-aos="fade-up"></div>
                <a href="#cta" class="btn btn-cta btn-lg"><i class="bi bi-star-fill me-2"></i> Garantir minha vaga</a>
            </div>
        </div>
    </section>

    <!-- ===================== CTA ===================== -->
    <section id="cta" class="cta">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="heading-2 mb-2">Inscreva-se Agora</div>
                    <?= $cta ?: '<div class="card-dark p-3">Pronto para começar? Clique em “Fazer minha inscrição”.</div>' ?>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-white-50 mb-1">Plano recomendado</div>
                                <div class="fs-3 fw-bold"><?= htmlspecialchars($nomeCurso ?: 'Excel para Concursos', ENT_QUOTES) ?></div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas Limitadas</span>
                        </div>

                        
                            <div class="display-6 fw-bold my-2" style="color:#00BB9C;">R$ <?= htmlspecialchars((string)$valorbruto) ?>/à vista</div>
                            <div class="small text-white-50 mb-3">ou Vitalício por R$ <?= htmlspecialchars((string)$valoravista) ?></div>
                       
                            <div class="display-6 fw-bold my-2" style="color:#00BB9C;">R$ <?= htmlspecialchars((string)$valoravista ?: '0,00') ?></div>
                            <div class="small text-white-50 mb-3">Acesso Vitalício com atualizações</div>
                       
                        <div class="d-grid gap-2">
                            <?php
                            // Monta link para vendas_inscricao preservando nav/ts/af via sessão/GET
                            $afForLink  = $_GET['af'] ?? ($_SESSION['af'] ?? '');
                            $tsForLink  = $_GET['ts'] ?? ($_SESSION['ts'] ?? '');
                            $navForLink = $_GET['nav'] ?? ($_SESSION['nav'] ?? '');
                            $qs = http_build_query(array_filter([
                                'nav' => $navForLink,
                                'ts'  => $tsForLink,
                                'af'  => $afForLink
                            ], fn($v) => $v !== '' && $v !== null));
                            ?>
                            <a class="btn btn-cta btn-lg" href="vendas_inscricao.php<?= $qs ? ('?' . $qs) : '' ?>">
                                <i class="bi bi-cart-check me-2"></i> Fazer minha inscrição
                            </a>

                            <a class="btn btn-outline-soft btn-lg" href="<?= htmlspecialchars($linkwhatsapp, ENT_QUOTES) ?> *<?= htmlspecialchars($nomeCurso ?: 'Excel para Concursos', ENT_QUOTES) ?>*" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp me-2"></i> Tirar dúvidas no WhatsApp
                            </a>
                        </div>

                        <div class="d-flex gap-3 mt-3 small text-white-50">
                            <div><i class="bi bi-shield-lock me-1"></i> Compra segura</div>
                            <div><i class="bi bi-credit-card me-1"></i> Pix / Cartão / Boleto</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="py-4 border-top border-opacity-25" style="border-color: rgba(255,255,255,.06) !important;">
        <div class="container small text-white-50 d-flex flex-wrap justify-content-between gap-2">
            <div>© <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.</div>
            <div class="d-flex gap-3">
                <a class="link-light link-underline-opacity-0" href="#">Termos</a>
                <a class="link-light link-underline-opacity-0" href="#">Privacidade</a>
                <a class="link-light link-underline-opacity-0" href="#">Contato</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true
        });
        document.getElementById('ano').textContent = new Date().getFullYear();
    </script>
</body>

</html>