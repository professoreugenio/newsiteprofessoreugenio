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
        $self = strtok($_SERVER['REQUEST_URI'], '?'); // esta mesma página, sem query
        header('Location: ' . $self);
        exit;
    }
}

/* ===================== Decodifica NAV -> idCursoVenda ===================== */
$idCursoVenda = 0;
$navRaw = $_SESSION['nav'] ?? '';
if ($navRaw !== '') {
    try {
        $decNavCurso = encrypt($navRaw, 'd'); // sua função de criptografia (d = decrypt)
    } catch (Throwable $e) {
        $decNavCurso = '';
    }
    if ($decNavCurso !== '') {
        parse_str($decNavCurso, $navParams);
        if (isset($navParams['id'])) {
            $idCursoVenda = (int)$navParams['id'];
        }
        if ($idCursoVenda === 0) {
            $exp = explode('&', $decNavCurso);
            $idCursoVenda = isset($exp[1]) ? (int)preg_replace('/\D+/', '', $exp[1]) : 0;
        }
    }
}
$idCursoVenda = max(0, (int)$idCursoVenda);

/* ===================== Defaults ===================== */
$enIdCurso = $enIdTurma = $Codigochave = '';
$Codigochave = '';
$youtubeurl = ''; // id do vídeo
$idCurso =   "";
$enIdCurso =   "";
$nomeTurma  =   "";
$idTurma    =   "";
$descricao   =   "";
$lead         =   "";
$chaveTurma    =   "";
$aovivo        =   "";
$horamanha    =   "";
$horatarde    =   "";
$horanoite     =   "";
$horasaulast            =   "";
$vendaliberada          =   "";
$chavepix               =   "";
$chavepixvalorvenda     =   "";
$valordocurso            =   "";
$valornocartao            =   "";
$valoravista    =   "";
$valorhoraaula         =   "";
$chavepixvitalicia     =   "";
$linkpagseguro         =   "";
$linkpagsegurovitalicia  =   "";
$linkmercadopago        =   "";
$linkmercadopagovitalicio  =   "";
$imgqrcodecurso         =   "";
$imgqrcodeanual         =   "";
$imgqrcodevitalicio     =   "";

/* ===================== Buscas (somente se idCursoVenda válido) ===================== */
if ($idCursoVenda > 0) {
    // TURMA (inclui codigoturma!)
    $q1 = $con->prepare("
        SELECT 
            t.codcursost,
            t.codigoturma       AS idturma,
            t.nometurma,
            t.aovivoct,
            t.horadem, t.horadet, t.horaden,
            t.valoranual, t.valorvendavitalicia,t.valorbrutoct,t.valorcartaoct,t.valoravistact,
            t.linkwhatsapp,
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
        $valordocurso             = $turma['valorbrutoct'] ?? '';
        $valornocartao             = $turma['valorcartaoct'] ?? '';
        $valoravista    = $turma['valoravistact'] ?? '';
        $valoranual            = $turma['valoranual'] ?? '';
        $valorvendavitalicia   = $turma['valorvendavitalicia'] ?? '';


        // WhatsApp
        $cel = preg_replace('/\D+/', '', (string)($turma['celularprofessorct'] ?? ''));
        if ($cel === '') $cel = '5585995637577';
        if (strpos($cel, '55') !== 0) $cel = '55' . $cel;
        $linkwhatsapp = 'https://wa.me/' . $cel . '?text=' . rawurlencode('Gostaria de mais informações sobre o curso');

        $chaveTurma = (string)($turma['chave'] ?? '');

        // CHAVE
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleção de Plano — Curso de Excel para Concursos | Professor Eugênio</title>
    <meta name="description"
        content="Escolha o plano do Curso de Excel para Concursos e prossiga para o pagamento. Opções Anual e Vitalício, Pix/Cartão/Boleto.">
    <link rel="canonical" href="https://professoreugenio.com/selecionar_plano">

    <!-- CSS: Bootstrap / Icons / AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Estilos do tema (paleta padrão) -->
    <style>
        :root {
            --c-h1: #00BB9C;
            /* h1 */
            --c-h2: #FF9C00;
            /* títulos tipo <h2> */
            --c-bg: #112240;
            /* fundo */
            --c-text: #ffffff;
            /* texto */
            --c-card: #0d1a34;
            /* cards */
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

        /* Barra de etapas (50% - etapa 2 de 4) */
        .steps {
            height: 6px;
            background: #1b2d55;
            border-radius: 999px;
            position: relative;
            overflow: hidden;
        }

        .steps>.progress {
            height: 100%;
            background: var(--c-h1);
            width: 50%;
        }

        /* Cartões de planos */
        .plan {
            position: relative;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .plan:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
        }

        .plan.recommended {
            border: 1px solid rgba(255, 156, 0, .55);
            box-shadow: 0 12px 32px rgba(255, 156, 0, .18);
        }

        .plan .price {
            color: #00BB9C;
        }

        .plan .check {
            color: #54e1c3;
        }

        .plan .radio-wrap {
            border: 1px dashed rgba(255, 255, 255, .18);
            border-radius: .75rem;
            padding: .5rem .75rem;
            background: rgba(17, 34, 64, .35);
        }

        .plan input[type="radio"] {
            width: 1.15rem;
            height: 1.15rem;
            accent-color: #FF9C00;
            cursor: pointer;
        }

        /* Botões */
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

        /* Tabela de comparação */
        .table-compare {
            --bs-table-color: #e9f3ff;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, .12);
        }

        .table-compare th,
        .table-compare td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <!-- ===================== NAV ===================== -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.html"><i class="bi bi-microsoft me-1"></i> Professor
                Eugênio</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navMain" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#planos">Planos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#comparativo">Comparativo</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-cta" href="#prosseguir"><i
                                class="bi bi-lightning-charge-fill me-1"></i> Continuar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== HEADER / ETAPA ===================== -->
    <section class="pt-5">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-journal-check me-1"></i> Etapa 2 de 4 — Seleção de Plano
                    </span>
                    <div class="heading-1 mb-2">Escolha seu Plano</div>
                    <p class="small-muted mb-2">Selecione abaixo a opção ideal para você. Você poderá pagar via Pix,
                        Cartão ou Boleto na próxima etapa.</p>
                    <div class="steps mt-3">
                        <div class="progress"></div>
                    </div>

                    af=<?php echo $_SESSION['af'] ?>
                    ts=<?php echo $_SESSION['ts'] ?>
                    nav=<?php echo $_SESSION['nav'] ?>
                </div>
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Curso</div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas
                                Limitadas</span>
                        </div>
                        <div class="fs-5 fw-bold mt-1">Excel para Concursos</div>
                        <div class="small text-white-50">Aulas ao vivo + gravadas • PDFs • Simulados • Certificado</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== PLANOS ===================== -->
    <!-- ===================== PLANOS ===================== -->
    <section id="planos">
        <div class="container">
            <?php
            // Helpers simples para preço
            $money = function ($v) {
                $v = (float)$v;
                return $v > 0 ? ('R$ ' . number_format($v, 2, ',', '.')) : '—';
            };
            $hasAnual      = ((float)$valoranual) > 0;
            $hasVitalicio  = ((float)$valorvendavitalicia) > 0;
            $hasAvista     = ((float)$valoravista) > 0;
            $hasCartao     = ((float)$valornocartao) > 0;

            // Para o anual, mostramos valor/ano e, como apoio, o mensal (dividido por 12, sem juros).
            $mensalAnual = $hasAnual ? ((float)$valoranual / 12.0) : 0.0;
            ?>

            <div class="row g-4">
                <!-- PLANO ANUAL -->
                <?php if ($hasAnual): ?>
                    <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="0">
                        <div class="card-dark plan h-100 p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="heading-2 mb-1">Plano Anual</div>
                                    <div class="price fs-3 fw-bold"><?= $money($valoranual) ?><span class="small text-white-50"> / ano</span></div>
                                    <div class="small text-white-50">
                                        ou <strong><?= $money($mensalAnual) ?></strong> / mês (referência)
                                    </div>
                                </div>
                                <span class="badge rounded-pill text-dark" style="background:#cdeee7;">Popular</span>
                            </div>

                            <ul class="small mt-3 mb-3">
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso por 12 meses</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Aulas ao vivo + gravadas</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>PDFs, simulados e certificado</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Suporte direto</li>
                            </ul>

                            <div class="radio-wrap d-flex align-items-center justify-content-between">
                                <label class="form-check-label" for="plano_anual"><strong>Selecionar Plano Anual</strong></label>
                                <input type="radio" name="plano" id="plano_anual" value="anual" aria-label="Plano Anual">
                            </div>

                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-soft btn-lg btn-select"
                                    data-plan="anual"
                                    data-valor="<?= $money($valoranual) ?> / ano">
                                    <i class="bi bi-check-circle me-2"></i> Selecionar Anual
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- PLANO VITALÍCIO -->
                <?php if ($hasVitalicio): ?>
                    <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="50">
                        <div class="card-dark plan recommended h-100 p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="heading-2 mb-1">Plano Vitalício</div>
                                    <div class="price fs-3 fw-bold"><?= $money($valorvendavitalicia) ?></div>
                                    <div class="small text-white-50">Pagamento único • Acesso permanente</div>
                                </div>
                                <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Melhor custo-benefício</span>
                            </div>

                            <ul class="small mt-3 mb-3">
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso vitalício</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Aulas ao vivo + gravadas</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>PDFs, simulados e certificado</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Suporte direto</li>
                            </ul>

                            <div class="radio-wrap d-flex align-items-center justify-content-between">
                                <label class="form-check-label" for="plano_vitalicio"><strong>Selecionar Plano Vitalício</strong></label>
                                <input type="radio" name="plano" id="plano_vitalicio" value="vitalicio" aria-label="Plano Vitalício">
                            </div>

                            <div class="d-grid mt-3">
                                <button class="btn btn-cta btn-lg btn-select"
                                    data-plan="vitalicio"
                                    data-valor="<?= $money($valorvendavitalicia) ?>">
                                    <i class="bi bi-check-circle-fill me-2"></i> Selecionar Vitalício
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- À VISTA (PIX/BOLETO) -->
                <?php if ($hasAvista): ?>
                    <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                        <div class="card-dark plan h-100 p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="heading-2 mb-1">À Vista</div>
                                    <div class="price fs-3 fw-bold"><?= $money($valoravista) ?></div>
                                    <div class="small text-white-50">Pix / Boleto • Melhor preço</div>
                                </div>
                                <span class="badge rounded-pill text-dark" style="background:#54e1c3;">Desconto</span>
                            </div>

                            <ul class="small mt-3 mb-3">
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Confirmação rápida</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso imediato</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Atualizações inclusas</li>
                            </ul>

                            <div class="radio-wrap d-flex align-items-center justify-content-between">
                                <label class="form-check-label" for="plano_avista"><strong>Selecionar À Vista (Pix/Boleto)</strong></label>
                                <input type="radio" name="plano" id="plano_avista" value="avista" aria-label="Plano À Vista">
                            </div>

                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-soft btn-lg btn-select"
                                    data-plan="avista"
                                    data-valor="<?= $money($valoravista) ?>">
                                    <i class="bi bi-check-circle me-2"></i> Selecionar À Vista
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- NO CARTÃO (PARCELADO) -->
                <?php if ($hasCartao): ?>
                    <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="150">
                        <div class="card-dark plan h-100 p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="heading-2 mb-1">No Cartão</div>
                                    <div class="price fs-3 fw-bold" style="color:#FFB64D;"><?= $money($valornocartao) ?></div>
                                    <div class="small text-white-50">Parcelamento disponível</div>
                                </div>
                                <span class="badge rounded-pill text-dark" style="background:#ffd58f;">Flexível</span>
                            </div>

                            <ul class="small mt-3 mb-3">
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Parcele em várias vezes</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso imediato</li>
                                <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Pagamento seguro</li>
                            </ul>

                            <div class="radio-wrap d-flex align-items-center justify-content-between">
                                <label class="form-check-label" for="plano_cartao"><strong>Selecionar No Cartão</strong></label>
                                <input type="radio" name="plano" id="plano_cartao" value="cartao" aria-label="Plano Cartão">
                            </div>

                            <div class="d-grid mt-3">
                                <button class="btn btn-outline-soft btn-lg btn-select"
                                    data-plan="cartao"
                                    data-valor="<?= $money($valornocartao) ?>">
                                    <i class="bi bi-check-circle me-2"></i> Selecionar No Cartão
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Aviso -->
            <div class="text-center small small-muted mt-4" data-aos="fade-up">
                Você poderá escolher <strong>Pix, Cartão ou Boleto</strong> na próxima etapa.
            </div>
        </div>
    </section>


    <!-- ===================== COMPARATIVO ===================== -->
    <section id="comparativo">
        <div class="container">
            <div class="text-center mb-3" data-aos="fade-up">
                <div class="heading-2">Comparativo rápido</div>
                <p class="small-muted mb-0">As duas opções entregam o mesmo conteúdo. A diferença está no período de
                    acesso e forma de cobrança.</p>
            </div>

            <div class="table-responsive" data-aos="fade-up" data-aos-delay="50">
                <table class="table table-compare align-middle">
                    <thead>
                        <tr>
                            <th width="38%">Recurso</th>
                            <th class="text-center">Anual</th>
                            <th class="text-center">Vitalício</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Acesso à plataforma</td>
                            <td class="text-center">12 meses</td>
                            <td class="text-center">Ilimitado</td>
                        </tr>
                        <tr>
                            <td>Aulas ao vivo + gravadas</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>PDFs, planilhas e simulados</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>Certificação digital</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>Forma de pagamento</td>
                            <td class="text-center">Recorrente mensal</td>
                            <td class="text-center">Único (uma vez)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ===================== CTA / PROSSEGUIR ===================== -->
    <section id="prosseguir">
        <div class="container">
            <div class="card-dark p-4" data-aos="fade-up">
                <div class="row gy-3 align-items-center">
                    <div class="col-lg-8">
                        <div class="heading-2 mb-1">Pronto para continuar?</div>
                        <p class="mb-0 small-muted">Clique em <strong>Prosseguir para Pagamento</strong>. Mantemos seus
                            dados salvos para agilizar a finalização.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid d-md-flex gap-2 justify-content-lg-end">
                            <a class="btn btn-outline-soft btn-lg" href="inscricao.html">
                                <i class="bi bi-arrow-left-circle me-2"></i> Voltar
                            </a>
                            <button class="btn btn-cta btn-lg" id="btnProsseguir">
                                <i class="bi bi-credit-card me-2"></i> Prosseguir para Pagamento
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="small text-white-50 mt-3">
                Dúvidas? <a class="link-light link-underline-opacity-0" target="_blank" rel="noopener"
                    href="https://wa.me/5585XXXXXXXX?text=Tenho%20d%C3%BAvidas%20sobre%20o%20plano%20do%20Curso%20de%20Excel%20para%20Concursos">Fale
                    no WhatsApp</a>.
            </p>
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

        // ---------- Estado / Persistência ----------
        const FIELDS = ['nome', 'email', 'telefone', 'objetivo', 'idCurso', 'idTurma', 'utm'];
        const getStore = (k) => {
            try {
                return localStorage.getItem('insc_' + k) || '';
            } catch (e) {
                return '';
            }
        };
        const setStore = (k, v) => {
            try {
                localStorage.setItem('insc_' + k, v);
            } catch (e) {}
        };

        // Determina um plano padrão (prioridade: avista > vitalicio > cartao > anual)
        const radios = Array.from(document.querySelectorAll('input[name="plano"]'));
        const has = (val) => radios.some(r => r.value === val);
        let planoAtual = has('avista') ? 'avista' :
            has('vitalicio') ? 'vitalicio' :
            has('cartao') ? 'cartao' :
            has('anual') ? 'anual' : '';

        if (planoAtual) {
            const r = document.querySelector(`input[name="plano"][value="${planoAtual}"]`);
            if (r) r.checked = true;
        }

        let valorPlanoFmt = (() => {
            const btn = document.querySelector(`.btn-select[data-plan="${planoAtual}"]`);
            return btn ? (btn.dataset.valor || '') : '';
        })();

        // Marca rádio ao clicar no botão "Selecionar"
        document.querySelectorAll('.btn-select').forEach(btn => {
            btn.addEventListener('click', () => {
                const plan = btn.dataset.plan;
                const valor = btn.dataset.valor || '';
                const radio = document.querySelector(`input[name="plano"][value="${plan}"]`);
                if (radio) radio.checked = true;
                planoAtual = plan;
                valorPlanoFmt = valor;
                setStore('planoSelecionado', plan);
                setStore('valorPlanoFmt', valor);

                btn.classList.add('disabled');
                setTimeout(() => btn.classList.remove('disabled'), 350);
            });
        });

        // Se o usuário clicar diretamente no radio
        document.querySelectorAll('input[name="plano"]').forEach(r => {
            r.addEventListener('change', () => {
                planoAtual = r.value;
                const btn = document.querySelector(`.btn-select[data-plan="${planoAtual}"]`);
                valorPlanoFmt = btn ? (btn.dataset.valor || '') : '';
                setStore('planoSelecionado', planoAtual);
                setStore('valorPlanoFmt', valorPlanoFmt);
            });
        });

        // UTM carry-over (se vier via querystring)
        const params = new URLSearchParams(location.search);
        const utm = params.get('utm');
        if (utm) setStore('utm', utm);

        // ---------- Próxima etapa ----------
        document.getElementById('btnProsseguir').addEventListener('click', () => {
            const checked = document.querySelector('input[name="plano"]:checked');
            if (!checked) {
                alert('Selecione um plano para continuar.');
                return;
            }

            const data = {
                plano: checked.value, // 'anual' | 'vitalicio' | 'avista' | 'cartao'
                valorPlanoFmt: valorPlanoFmt, // Ex.: "R$ 399,00 / ano", "R$ 85,00"
                idCurso: getStore('idCurso') || '',
                idTurma: getStore('idTurma') || '',
                nome: getStore('nome') || '',
                email: getStore('email') || '',
                telefone: getStore('telefone') || '',
                objetivo: getStore('objetivo') || '',
                utm: getStore('utm') || ''
            };

            setStore('planoSelecionado', data.plano);
            setStore('valorPlanoFmt', data.valorPlanoFmt);

            const qs = new URLSearchParams(data).toString();
            window.location.href = 'vendaPagamento.php?' + qs;
        });
    </script>

</body>

</html>