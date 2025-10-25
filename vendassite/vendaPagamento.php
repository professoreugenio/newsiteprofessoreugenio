<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1)); // ajuste se necessário

/* ===================== INCLUDES DO PROJETO ===================== */
require_once APP_ROOT . '/conexao/class.conexao.php';   // $con = config::connect();
require_once APP_ROOT . '/autenticacao.php';            // se precisar (ex.: utilitários de sessão/login)

/* ===================== CONFIG DE SESSÃO (4 HORAS) ===================== */
const SESSION_TTL = 4 * 3600; // 4 horas

session_set_cookie_params([
    'lifetime' => SESSION_TTL,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// if (!isset($_SESSION['session_started_at'])) {
//     $_SESSION['session_started_at'] = time();
// } elseif ((time() - (int)$_SESSION['session_started_at']) > SESSION_TTL) {
//     unset($_SESSION['nav'], $_SESSION['nav_set_at'], $_SESSION['af'], $_SESSION['af_set_at'], $_SESSION['ts'], $_SESSION['prg_redirect_done']);
//     session_regenerate_id(true);
//     $_SESSION['session_started_at'] = time();
// }

/* ===================== Helpers ===================== */
function get_param(string $k): ?string
{
    if (!array_key_exists($k, $_GET)) return null;
    $v = trim((string)$_GET[$k]);
    if ($v === '') return null;
    if (strlen($v) > 8192) $v = substr($v, 0, 8192);
    return $v;
}
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function fmtHora(?string $h): ?string
{
    if (!$h || $h === '00:00:00') return null;
    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $h) === 1) return substr($h, 0, 5);
    return $h;
}

/* ===================== Captura GET -> Sessão (PRG) ===================== */
$paramNav = get_param('nav');
$paramAf  = get_param('af');
$paramTs  = get_param('ts');

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

$hasNewParams   = ($paramNav !== null) || ($paramAf !== null) || ($paramTs !== null);
$prgAlreadyDone = !empty($_SESSION['prg_redirect_done']);
$noredir        = isset($_GET['noredir']) && $_GET['noredir'] == '1';

if ($hasNewParams && !$prgAlreadyDone && !$noredir) {
    $_SESSION['prg_redirect_done'] = time();
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $self = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $self);
        exit;
    }
}

/* ===================== Decodifica NAV -> idCursoVenda ===================== */
$idCursoVenda = 0;
$navRaw = $_SESSION['nav'] ?? '';
if ($navRaw !== '') {
    try {
        $decNavCurso = encrypt($navRaw, 'd');
    } catch (Throwable $e) {
        $decNavCurso = '';
    }
    if ($decNavCurso !== '') {
        parse_str($decNavCurso, $navParams);
        if (isset($navParams['id'])) $idCursoVenda = (int)$navParams['id'];
        if ($idCursoVenda === 0) {
            $exp = explode('&', $decNavCurso);
            $idCursoVenda = isset($exp[1]) ? (int)preg_replace('/\D+/', '', $exp[1]) : 0;
        }
    }
}
$idCursoVenda = max(0, (int)$idCursoVenda);

/* ===================== Defaults ===================== */
$enIdCurso = $enIdTurma = $Codigochave = '';
$youtubeurl = '';
$idCurso = $enIdCurso = $nomeTurma = $idTurma = $descricao = $lead = $chaveTurma = '';
$aovivo = $horamanha = $horatarde = $horanoite = $horasaulast = $vendaliberada = '';
$chavepix = $chavepixvalorvenda = $valordocurso = $valornocartao = $valoravista = $valorhoraaula = '';
$chavepixvitalicia = $linkpagseguro = $linkpagsegurovitalicia = $linkmercadopago = $linkmercadopagovitalicio = '';
$imgqrcodecurso = $imgqrcodeanual = $imgqrcodevitalicio = '';
$valoranual = $valorvendavitalicia = '';
$nomeCurso = '';
$linkwhatsapp = '';

/* ===================== Buscas (somente se idCursoVenda válido) ===================== */
if ($idCursoVenda > 0) {
    // TURMA
    $q1 = $con->prepare("
        SELECT 
            t.codcursost,
            t.codigoturma       AS idturma,
            t.nometurma,
            t.aovivoct,
            t.horadem, t.horadet, t.horaden,
            t.valoranual, t.valorvendavitalicia, t.valorbrutoct, t.valorcartaoct, t.valoravistact,
            t.linkwhatsapp,
            t.imgqrcodecurso, t.imgqrcodeanual, t.imgqrcodevitalicio,
            t.celularprofessorct,
            t.chave AS chave
        FROM new_sistema_cursos_turmas t
        INNER JOIN new_sistema_chave c ON c.chaveturmasc = t.chave
        WHERE t.codcursost = :id AND t.comercialt = '1'
        LIMIT 1
    ");
    $q1->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
    $q1->execute();
    $turma = $q1->fetch(PDO::FETCH_ASSOC);

    if ($turma) {
        $idCurso   = (string)($turma['codcursost'] ?? '');
        $idTurma   = (string)($turma['idturma'] ?? '');
        $enIdCurso = $idCurso !== '' ? encrypt($idCurso, 'e') : '';
        $enIdTurma = $idTurma !== '' ? encrypt($idTurma, 'e') : '';

        $aovivo       = (int)($turma['aovivoct'] ?? 0);
        $horamanha    = (string)($turma['horadem'] ?? '');
        $horatarde    = (string)($turma['horadet'] ?? '');
        $horanoite    = (string)($turma['horaden'] ?? '');

        $valordocurso         = $turma['valorbrutoct'] ?? '';
        $valornocartao        = $turma['valorcartaoct'] ?? '';
        $valoravista          = $turma['valoravistact'] ?? '';
        $valoranual           = $turma['valoranual'] ?? '';
        $valorvendavitalicia  = $turma['valorvendavitalicia'] ?? '';

        // QR codes
        $imgqrcodecurso     = (string)($turma['imgqrcodecurso'] ?? '');
        $imgqrcodeanual     = (string)($turma['imgqrcodeanual'] ?? '');
        $imgqrcodevitalicio = (string)($turma['imgqrcodevitalicio'] ?? '');


        $valoravista = $turma['valoravistact'] ?? '';
        $pixvaloravistact = (string)($turma['pixvaloravistact'] ?? '');
        $valoranual = $turma['valoranual'] ?? '';
        $pixvaloranualavistact = (string)($turma['pixvaloranualavistact'] ?? '');


        // Critério 1: à vista (ou valor cheio)
        if ((float)$valordocurso > 0 || (float)$valoravista > 0) {
            $pixvaloravistact = (string)($turma['pixvaloravistact'] ?? '');
        }

        // Critério 2: plano anual
        if ((float)$valoranual > 0) {
            $pixvaloranualavistact = (string)($turma['pixvaloranualavistact'] ?? '');
        }

        // Critério 3: plano vitalício (se desejar manter a mesma lógica)
        if ((float)($turma['valorvendavitalicia'] ?? 0) > 0) {
            $pixvalorvitalicioact = (string)($turma['pixvalorvitalicioact'] ?? '');
        }




        // WhatsApp
        $cel = preg_replace('/\D+/', '', (string)($turma['celularprofessorct'] ?? ''));
        if ($cel === '') $cel = '5585995637577';
        if (strpos($cel, '55') !== 0) $cel = '55' . $cel;
        $linkwhatsapp = 'https://wa.me/' . $cel . '?text=' . rawurlencode('Gostaria de mais informações sobre o curso');

        $chaveTurma = (string)($turma['chave'] ?? '');
        if ($chaveTurma !== '') {
            $qCh = $con->prepare("SELECT chavesc FROM new_sistema_chave WHERE chaveturmasc = :campo LIMIT 1");
            $qCh->bindValue(':campo', $chaveTurma, PDO::PARAM_STR);
            $qCh->execute();
            $rwCh = $qCh->fetch(PDO::FETCH_ASSOC);
            if ($rwCh && !empty($rwCh['chavesc'])) {
                $Codigochave = encrypt($rwCh['chavesc'], 'e');
            }
        }
    }

    // CURSO (nome)
    $q2 = $con->prepare("SELECT nomecurso FROM new_sistema_cursos WHERE codigocursos = :id LIMIT 1");
    $q2->bindValue(':id', $idCursoVenda, PDO::PARAM_INT);
    $q2->execute();
    $curso = $q2->fetch(PDO::FETCH_ASSOC);
    if ($curso) {
        $nomeCurso = (string)($curso['nomecurso'] ?? '');
    }
}

/* ===================== Horários ===================== */
$hasManha  = (bool)fmtHora($horamanha);
$hasTarde  = (bool)fmtHora($horatarde);
$hasNoite  = (bool)fmtHora($horanoite);
$hasAnyTime = $hasManha || $hasTarde || $hasNoite;

/* ===================== Afiliado ===================== */
$CodigoAfiliadoVal = $_GET['af'] ?? ($_SESSION['af'] ?? '');

/* ===================== PIX: Seleção do QR conforme o plano ===================== */
$planoSel = $_GET['plano'] ?? ''; // 'avista' | 'anual' | 'vitalicio' | 'cartao'
$qrImgUrl = '';                   // URL final do QR
$raizSite = 'https://professoreugenio.com'; // ajuste se necessário

/**
 * Fallback de busca na tabela de mídias caso a URL do QR não exista no cadastro da turma.
 * MAPEAMENTO do tipo (ajuste se seu banco usar outros valores):
 *   avista   -> tipo 1
 *   anual    -> tipo 2
 *   vitalicio-> tipo 3
 */
function buscarQrPorTipo(PDO $con, string $idTurma, int $tipo, string $raizSite): ?string
{
    $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA WHERE codpublicacao = :idturma AND tipo = :tipo LIMIT 1");
    $query->bindParam(":idturma", $idTurma);
    $query->bindParam(":tipo", $tipo, PDO::PARAM_INT);
    $query->execute();
    $rwImg = $query->fetch(PDO::FETCH_ASSOC);
    if ($rwImg && !empty($rwImg['pasta']) && !empty($rwImg['foto'])) {
        $pasta = $rwImg['pasta'];
        $foto  = $rwImg['foto'];
        return rtrim($raizSite, '/') . "/fotos/midias/{$pasta}/{$foto}";
    }
    return null;
}

// Decide qual QR usar
if ($planoSel === 'avista' && (float)$valoravista > 0) {
    if (!empty($imgqrcodecurso)) {
        $qrImgUrl = $imgqrcodecurso;
    } else {
        $qrImgUrl = buscarQrPorTipo($con, $idTurma, 1, $raizSite) ?? '';
    }
} elseif ($planoSel === 'anual' && (float)$valoranual > 0) {
    if (!empty($imgqrcodeanual)) {
        $qrImgUrl = $imgqrcodeanual;
    } else {
        $qrImgUrl = buscarQrPorTipo($con, $idTurma, 2, $raizSite) ?? '';
    }
} elseif ($planoSel === 'vitalicio' && (float)$valorvendavitalicia > 0) {
    if (!empty($imgqrcodevitalicio)) {
        $qrImgUrl = $imgqrcodevitalicio;
    } else {
        $qrImgUrl = buscarQrPorTipo($con, $idTurma, 3, $raizSite) ?? '';
    }
}
// Para 'cartao', mantemos $qrImgUrl vazio (sem QR Pix).
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento — Curso de Excel para Concursos | Professor Eugênio</title>
    <meta name="description" content="Finalize o pagamento do Curso de Excel para Concursos. Opções Pix, Cartão ou Boleto. Compra segura e suporte.">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --c-h1: #00BB9C;
            --c-h2: #FF9C00;
            --c-bg: #112240;
            --c-text: #ffffff;
            --c-card: #0d1a34;
            --c-muted: #9fb1d1;
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
            scroll-behavior: smooth;
        }

        .navbar {
            backdrop-filter: saturate(140%) blur(6px);
            background: rgba(17, 34, 64, .85);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .navbar .nav-link {
            color: #e6f2ff;
        }

        .navbar .nav-link:hover {
            color: var(--c-h1);
        }

        .heading-1 {
            color: var(--c-h1);
            font-weight: 800;
            letter-spacing: .2px;
        }

        .heading-2 {
            color: var(--c-h2);
            font-weight: 800;
            letter-spacing: .2px;
            font-size: clamp(1.2rem, 2.5vw, 1.6rem);
            text-transform: uppercase;
        }

        .small-muted {
            color: var(--c-muted);
        }

        section {
            padding: 64px 0;
        }

        .card-dark {
            background: var(--c-card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #e9f3ff;
        }

        .nav-pills .nav-link {
            color: #e6f2ff;
            border: 1px solid rgba(255, 255, 255, .12);
            background: #0f1f3d;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #153060, #0f2244);
            border-color: rgba(255, 255, 255, .22);
        }

        .form-control,
        .form-select {
            background: #0f1f3d;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .15);
        }

        .form-control:focus,
        .form-select:focus {
            background: #0f244a;
            color: #fff;
            border-color: #5bdac1;
            box-shadow: 0 0 0 .2rem rgba(0, 187, 156, .15);
        }

        .btn-cta {
            background: linear-gradient(135deg, #FF9C00, #ffb547);
            color: #112240;
            font-weight: 800;
            border: none;
            box-shadow: 0 10px 24px rgba(255, 156, 0, .25);
        }

        .btn-cta:hover {
            filter: brightness(1.05);
            color: #0b1730;
        }

        .btn-outline-soft {
            border: 1px solid rgba(255, 255, 255, .25);
            color: #e9f3ff;
        }

        .btn-outline-soft:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .pix-box {
            background: #0e2144;
            border: 1px dashed rgba(255, 255, 255, .2);
            border-radius: 1rem;
        }

        .table-summary {
            --bs-table-color: #e9f3ff;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, .12);
        }

        .table-summary th,
        .table-summary td {
            vertical-align: middle;
        }

        .trust li {
            list-style: none;
            margin: 6px 0;
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.html"><i class="bi bi-microsoft me-1"></i> Professor Eugênio</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navMain" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#resumo">Resumo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pagamento">Pagamento</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-cta" href="#pagamento"><i class="bi bi-credit-card me-1"></i> Finalizar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <section class="pt-5">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-wallet2 me-1"></i> Etapa 3 de 4 — Pagamento
                    </span>
                    <div class="heading-1 mb-2">Finalize seu Pagamento</div>
                    <p class="small-muted mb-0">Escolha Pix, Cartão ou Boleto. Compra segura e suporte durante todo o processo.</p>
                    <?php echo $_SESSION['nav'];  ?>
                </div>
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Curso</div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas Limitadas</span>
                        </div>
                        <div class="fs-5 fw-bold mt-1"><?= h($nomeCurso ?: 'Excel para Concursos') ?></div>
                        <div class="small text-white-50">Aulas ao vivo + gravadas • PDFs • Simulados • Certificado</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RESUMO -->
    <section id="resumo">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card-dark p-4">
                        <div class="heading-2 mb-2">Resumo do Pedido</div>
                        <div class="table-responsive">
                            <table class="table table-summary align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th width="30%">Plano</th>
                                        <td id="resPlano">—</td>
                                    </tr>
                                    <tr>
                                        <th>Valor</th>
                                        <td id="resValor">—</td>
                                    </tr>
                                    <tr>
                                        <th>Aluno</th>
                                        <td id="resNome">—</td>
                                    </tr>
                                    <tr>
                                        <th>E-mail</th>
                                        <td id="resEmail">—</td>
                                    </tr>
                                    <tr>
                                        <th>WhatsApp</th>
                                        <td id="resTelefone">—</td>
                                    </tr>
                                    <tr>
                                        <th>Concurso-alvo</th>
                                        <td id="resObjetivo">—</td>
                                    </tr>
                                    <tr>
                                        <th>Turma</th>
                                        <td id="resTurma">—</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-white-50 mt-3 mb-0">
                            Ao concluir, você receberá seu acesso imediatamente no e-mail informado (verifique caixa de entrada e spam).
                        </p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Garantia</div>
                            <i class="bi bi-shield-check fs-4" style="color:#00BB9C"></i>
                        </div>
                        <div class="fs-6 fw-bold mt-1">7 dias de garantia</div>
                        <ul class="trust small mt-2 mb-0">
                            <li><i class="bi bi-lock me-2"></i>Ambiente seguro e criptografado</li>
                            <li><i class="bi bi-credit-card-2-front me-2"></i>Pix, Cartão ou Boleto</li>
                            <li><i class="bi bi-headset me-2"></i>Suporte por WhatsApp e e-mail</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PAGAMENTO -->
    <section id="pagamento">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8" data-aos="fade-up">
                    <ul class="nav nav-pills gap-2 mb-3" id="payTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pix-tab" data-bs-toggle="pill" data-bs-target="#pix" type="button" role="tab">
                                <i class="bi bi-qr-code me-1"></i> Pix
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="card-tab" data-bs-toggle="pill" data-bs-target="#card" type="button" role="tab">
                                <i class="bi bi-credit-card me-1"></i> Cartão
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="boleto-tab" data-bs-toggle="pill" data-bs-target="#boleto" type="button" role="tab">
                                <i class="bi bi-file-earmark-text me-1"></i> Boleto
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- PIX -->
                        <?php
                        // ================== Seleção da imagem e chave PIX conforme valores ==================
                        $qrImgUrl = '';
                        $pixCode  = '';
                        $pixValor = 'R$ 0,00';

                      echo  $pixCode  = (string)($turma['pixvaloranualavistact'] ?? '');

                                    // Critério 1: valor à vista ou valor base (curso avulso)
                                    // PIX ANUAL
                                    if ((float)$valoranual > 0) {
                                        $qrImgUrl = !empty($imgqrcodeanual)
                                            ? $imgqrcodeanual
                                            : 'https://via.placeholder.com/480x480.png?text=QR+Code+Pix';
                                        $pixCode  = (string)($turma['pixvaloranualavistact'] ?? '');
                                        $pixValor = 'R$ ' . number_format((float)$valoranual, 2, ',', '.');
                                    }

                                    // PIX VITALÍCIO
                                    elseif ((float)$valorvendavitalicia > 0) {
                                        $qrImgUrl = !empty($imgqrcodevitalicio)
                                            ? $imgqrcodevitalicio
                                            : 'https://via.placeholder.com/480x480.png?text=QR+Code+Pix';
                                        $pixCode  = (string)($turma['pixvaloravistact'] ?? '');
                                        $pixValor = 'R$ ' . number_format((float)$valorvendavitalicia, 2, ',', '.');
                                    }

                                    // CASO NÃO TENHA PIX DEFINIDO
                                    else {
                                        $qrImgUrl = 'https://via.placeholder.com/480x480.png?text=QR+Code+Pix';
                                        $pixCode  = 'CHAVE PIX INDISPONÍVEL';
                                    }
                        ?>

                        <!-- ================== BLOCO PIX ================== -->
                        <div class="tab-pane fade show active" id="pix" role="tabpanel">
                            <div class="card-dark p-4">
                                <div class="heading-2 mb-2">Pague via Pix</div>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <div class="pix-box p-3 text-center">
                                            <?php if (!empty($qrImgUrl)): ?>
                                                <img src="<?= h($qrImgUrl) ?>" class="img-fluid rounded" alt="QR Code Pix">
                                            <?php else: ?>
                                                <img src="https://via.placeholder.com/480x480.png?text=QR+Code+Pix" class="img-fluid rounded" alt="QR Code Pix indisponível">
                                                <div class="small text-white-50 mt-2">QR Code indisponível para este plano/momento.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="small-muted mb-2">
                                            Abra o app do seu banco, escolha <strong>Pix &gt; Pagar</strong> e aponte a câmera para o QR Code.
                                        </p>
                                        <div class="mb-2">
                                            <label class="form-label small">Código Pix copia e cola</label>
                                            <div class="input-group">
                                                <input id="pixCode" class="form-control" readonly value="<?= h($pixCode) ?>">
                                                <button class="btn btn-outline-soft" id="btnCopyPix"><i class="bi bi-clipboard"></i></button>
                                            </div>
                                            <small class="text-white-50">Valor: <span id="pixValor"><?= h($pixValor) ?></span></small>
                                        </div>
                                        <div class="d-grid d-md-flex gap-2 mt-3">
                                            <button class="btn btn-cta" id="btnPixPagar"><i class="bi bi-check-circle me-2"></i> Confirmar Pagamento Pix</button>
                                            <a class="btn btn-outline-soft" href="#resumo"><i class="bi bi-arrow-left-circle me-2"></i> Voltar ao Resumo</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- CARTÃO -->
                        <div class="tab-pane fade" id="card" role="tabpanel">
                            <div class="card-dark p-4">
                                <div class="heading-2 mb-2">Pague com Cartão</div>
                                <form id="formCartao" class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small">Nome impresso no cartão</label>
                                        <input type="text" class="form-control" id="ccNome" placeholder="Ex.: JOAO DA SILVA" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Número do cartão</label>
                                        <input type="text" class="form-control" id="ccNumero" inputmode="numeric" placeholder="0000 0000 0000 0000" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Validade (MM/AA)</label>
                                        <input type="text" class="form-control" id="ccValidade" inputmode="numeric" placeholder="12/29" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">CVV</label>
                                        <input type="password" class="form-control" id="ccCVV" inputmode="numeric" placeholder="123" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Parcelas</label>
                                        <select class="form-select" id="ccParcelas">
                                            <option value="1">1x sem juros</option>
                                            <option value="2">2x sem juros</option>
                                            <option value="3">3x sem juros</option>
                                        </select>
                                    </div>
                                    <div class="col-12 d-grid d-md-flex gap-2">
                                        <button type="submit" class="btn btn-cta"><i class="bi bi-lock-fill me-2"></i> Pagar com Cartão</button>
                                        <a class="btn btn-outline-soft" href="#resumo"><i class="bi bi-arrow-left-circle me-2"></i> Voltar ao Resumo</a>
                                    </div>
                                </form>
                                <p class="small text-white-50 mb-0 mt-2">Pagamento seguro. Seus dados não serão armazenados neste site.</p>
                            </div>
                        </div>

                        <!-- BOLETO -->
                        <div class="tab-pane fade" id="boleto" role="tabpanel">
                            <div class="card-dark p-4">
                                <div class="heading-2 mb-2">Gerar Boleto</div>
                                <p class="small-muted mb-3">Ao gerar o boleto, você terá até 2 dias úteis para pagar. O acesso é liberado após a compensação.</p>
                                <div class="d-grid d-md-flex gap-2">
                                    <button class="btn btn-cta" id="btnGerarBoleto"><i class="bi bi-receipt-cutoff me-2"></i> Gerar Boleto (simulação)</button>
                                    <a class="btn btn-outline-soft" href="#resumo"><i class="bi bi-arrow-left-circle me-2"></i> Voltar ao Resumo</a>
                                </div>
                                <div id="boletoBox" class="mt-3 d-none">
                                    <div class="pix-box p-3">
                                        <div class="small-muted">Linha digitável do boleto</div>
                                        <div class="input-group mt-1">
                                            <input id="linhaBoleto" class="form-control" readonly value="">
                                            <button class="btn btn-outline-soft" id="btnCopyBoleto"><i class="bi bi-clipboard"></i></button>
                                        </div>
                                        <small class="text-white-50">Valor: <span id="boletoValor">R$ 0,00</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /tab-content -->
                </div>

                <!-- Lateral de suporte -->
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="fw-bold mb-2"><i class="bi bi-headset me-2"></i>Precisa de ajuda?</div>
                        <p class="small small-muted mb-3">Fale diretamente com o professor para orientação rápida.</p>
                        <a class="btn btn-outline-light w-100" target="_blank" rel="noopener"
                            href="<?= h($linkwhatsapp ?: 'https://wa.me/5585995637577') ?>">
                            Chamar no WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER -->
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

        // ---------- Storage / Query ----------
        function getStore(k) {
            try {
                return localStorage.getItem('insc_' + k) || '';
            } catch (e) {
                return '';
            }
        }

        function setStore(k, v) {
            try {
                localStorage.setItem('insc_' + k, v);
            } catch (e) {}
        }
        const params = new URLSearchParams(location.search);
        const q = (k) => params.get(k) || '';

        const data = {
            plano: q('plano') || getStore('planoSelecionado') || 'vitalicio', // anual | vitalicio | avista | cartao
            valor: q('valorPlanoFmt') || getStore('valorPlanoFmt') || 'R$ 0,00',
            nome: q('nome') || getStore('nome') || '',
            email: q('email') || getStore('email') || '',
            telefone: q('telefone') || getStore('telefone') || '',
            objetivo: q('objetivo') || getStore('objetivo') || '',
            idTurma: q('idTurma') || getStore('idTurma') || '',
            idCurso: q('idCurso') || getStore('idCurso') || '',
            utm: q('utm') || getStore('utm') || ''
        };

        // Exibe no resumo
        const nomesPlanos = {
            anual: 'Anual',
            vitalicio: 'Vitalício',
            avista: 'À vista (Pix/Boleto)',
            cartao: 'Cartão'
        };
        document.getElementById('resPlano').textContent = nomesPlanos[data.plano] || '—';
        document.getElementById('resValor').textContent = data.valor;
        document.getElementById('resNome').textContent = data.nome || '—';
        document.getElementById('resEmail').textContent = data.email || '—';
        document.getElementById('resTelefone').textContent = data.telefone || '—';
        document.getElementById('resObjetivo').textContent = data.objetivo || '—';
        document.getElementById('resTurma').textContent = data.idTurma || '—';

        // Atualiza valores nas seções Pix/Boleto
        document.getElementById('pixValor').textContent = data.valor;
        document.getElementById('boletoValor').textContent = data.valor;

        // PIX: copiar código (exemplo estático; substitua pelo seu "copia e cola" real, se disponível)
        document.getElementById('btnCopyPix').addEventListener('click', () => {
            const el = document.getElementById('pixCode');
            el.select();
            el.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert('Código Pix copiado!');
        });

        // Simulação de confirmação Pix (UI)
        document.getElementById('btnPixPagar').addEventListener('click', () => {
            alert('Pagamento via Pix em processamento.\nApós confirmação, seu acesso será liberado automaticamente.');
        });

        // CARTÃO: validações simples de UI
        document.getElementById('formCartao').addEventListener('submit', (e) => {
            e.preventDefault();
            const num = document.getElementById('ccNumero').value.replace(/\D/g, '');
            const val = document.getElementById('ccValidade').value.trim();
            const cvv = document.getElementById('ccCVV').value.trim();
            if (num.length < 13 || num.length > 19) return alert('Número de cartão inválido.');
            if (!/^\d{2}\/\d{2}$/.test(val)) return alert('Validade inválida. Use MM/AA.');
            if (!/^\d{3,4}$/.test(cvv)) return alert('CVV inválido.');
            alert('Pagamento com cartão enviado para processamento (simulação).');
        });

        // BOLETO (simulação)
        function gerarLinhaBoleto() {
            const base = '23790' + Math.floor(1000000000000000 + Math.random() * 9000000000000000);
            return base + Math.floor(Math.random() * 10);
        }
        document.getElementById('btnGerarBoleto').addEventListener('click', () => {
            const linha = gerarLinhaBoleto();
            document.getElementById('linhaBoleto').value = linha;
            document.getElementById('boletoBox').classList.remove('d-none');
        });
        document.getElementById('btnCopyBoleto').addEventListener('click', () => {
            const el = document.getElementById('linhaBoleto');
            el.select();
            el.setSelectionRange(0, 99999);
            document.execCommand('copy');
            alert('Linha digitável copiada!');
        });

        // Persiste
        setStore('planoSelecionado', data.plano);
        setStore('valorPlanoFmt', data.valor);
        if (data.idCurso) setStore('idCurso', data.idCurso);
        if (data.idTurma) setStore('idTurma', data.idTurma);
        if (data.nome) setStore('nome', data.nome);
        if (data.email) setStore('email', data.email);
        if (data.telefone) setStore('telefone', data.telefone);
        if (data.objetivo) setStore('objetivo', data.objetivo);
        if (data.utm) setStore('utm', data.utm);
    </script>
</body>

</html>