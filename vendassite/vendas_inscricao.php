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
    <!-- Metadados -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrição — Curso <?= h($nomeCurso ?: 'Excel para Concursos') ?> | Professor Eugênio</title>
    <meta name="description" content="Faça sua inscrição para o Curso de <?= h($nomeCurso ?: 'Excel para Concursos') ?>. Aulas ao vivo e gravadas, material para download e certificação.">
    <!-- Bootstrap / Icons / AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --c-h1: #00BB9C;
            --c-h2: #FF9C00;
            --c-bg: #112240;
            --c-text: #fff;
            --c-card: #0d1a34;
            --c-muted: #9fb1d1;
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
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

        .steps {
            height: 6px;
            background: #1b2d55;
            border-radius: 999px;
            overflow: hidden;
        }

        .steps>.progress {
            height: 100%;
            background: var(--c-h1);
            width: 25%;
        }

        .form-floating>label {
            color: #cfe2ff;
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

        .is-invalid {
            border-color: #ff6b6b !important;
        }

        .invalid-feedback {
            color: #ffd0d0;
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

        .small-muted {
            color: var(--c-muted);
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .75rem;
            border-radius: 999px;
            font-weight: 800;
            background: linear-gradient(135deg, #14ccab, #0bb598);
            color: #0b1832;
            box-shadow: 0 6px 20px rgba(20, 204, 171, .25);
            border: 1px solid rgba(20, 204, 171, .35);
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .opt-time {
            background: #0f1f3d;
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: .75rem;
            padding: .75rem;
        }

        .opt-time input {
            accent-color: #FF9C00;
        }

        .form-hint {
            color: #9fb1d1;
            font-size: .92rem;
        }
    </style>
</head>

<body>
    <!-- ===================== NAV ===================== -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php"><i class="bi bi-microsoft me-1"></i> Professor Eugênio</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navMain" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#inscricao">Inscrição</a></li>
                    <li class="nav-item"><a class="nav-link" href="#seguranca">Segurança</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-cta" href="#inscricao"><i class="bi bi-lightning-charge-fill me-1"></i> Começar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== HERO RESUMO ===================== -->
    <section class="pt-5">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-7" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-trophy me-1"></i> Curso de <?= h($nomeCurso ?: 'Excel para Concursos') ?>
                    </span>
                    <h1 class="heading-1 mb-2">Faça sua Inscrição</h1>
                    <p class="small-muted mb-0">Preencha seus dados para reservar sua vaga. Você poderá escolher o plano e finalizar o pagamento na próxima etapa.</p>
                    <div class="steps mt-3">
                        <div class="progress"></div>
                    </div>
                    <div class="d-flex gap-3 mt-3 small text-white-50">
                        <div><i class="bi bi-camera-video me-1"></i> Aulas ao vivo + gravadas</div>
                        <div><i class="bi bi-patch-check me-1"></i> Certificação</div>
                        <div><i class="bi bi-file-earmark-arrow-down me-1"></i> PDFs e simulados</div>
                    </div>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div class="small text-white-50 mb-1">Plano recomendado</div>
                                <div class="fs-4 fw-bold"><?= h($nomeCurso ?: 'Desenvolvimento Web (PHP)') ?></div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas Limitadas</span>
                        </div>

                        <?php
                        // ------- Preparação de preços -------
                        $precoBase   = isset($valordocurso)   ? (float)$valordocurso   : 0.0;
                        $precoVista  = isset($valoravista)    ? (float)$valoravista    : 0.0;
                        $precoCartao = isset($valornocartao)  ? (float)$valornocartao  : 0.0;

                        $fmt = fn(float $v) => 'R$ ' . number_format($v, 2, ',', '.');
                        $pct = function (float $de, float $para): ?int {
                            if ($de <= 0 || $para <= 0 || $para >= $de) return null;
                            return (int)round((1 - ($para / $de)) * 100);
                        };

                        $offVista  = $pct($precoBase, $precoVista);
                        $offCartao = $pct($precoBase, $precoCartao);
                        ?>

                        <!-- Valor base (riscado) -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Valor do curso</div>
                            <div class="text-white-50" style="text-decoration:line-through;">
                                <?= $precoBase > 0 ? $fmt($precoBase) : '—' ?>
                            </div>
                        </div>

                        <?php if ($precoVista > 0): ?>
                            <!-- Destaque: À vista -->
                            <div class="mt-3 p-3 rounded-3"
                                style="background:linear-gradient(135deg, rgba(84,225,195,.12), rgba(84,225,195,.06)); border:1px solid rgba(84,225,195,.25);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge rounded-pill text-dark" style="background:#54e1c3; font-weight:800;">Melhor preço à vista</span>
                                    <?php if (!is_null($offVista)): ?>
                                        <span class="badge rounded-pill"
                                            style="background:rgba(84,225,195,.15); border:1px solid rgba(84,225,195,.45); color:#54e1c3;">
                                            -<?= $offVista ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex align-items-end gap-2 mt-2">
                                    <div class="display-5 fw-bold" style="color:#00BB9C; line-height:1;">
                                        <?= $fmt($precoVista) ?>
                                    </div>
                                    <div class="small text-white-50 mb-2">à vista no Pix/Boleto</div>
                                </div>
                            </div>

                            <!-- Cartão -->
                            <div class="mt-3 p-3 rounded-3"
                                style="background:linear-gradient(135deg, rgba(255,156,0,.08), rgba(255,156,0,.03)); border:1px solid rgba(255,156,0,.25);">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="small text-white-50">No cartão de crédito</div>
                                    <?php if (!is_null($offCartao)): ?>
                                        <span class="badge rounded-pill"
                                            style="background:rgba(255,156,0,.15); border:1px solid rgba(255,156,0,.45); color:#FF9C00;">
                                            -<?= $offCartao ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="fs-4 fw-semibold" style="color:#FFB64D; line-height:1;">
                                    <?= $precoCartao > 0 ? $fmt($precoCartao) : 'Consulte' ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="small text-white-50 mt-2">Vitalício com atualizações</div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FORMULÁRIO DE INSCRIÇÃO ===================== -->
    <section id="inscricao">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-7" data-aos="fade-up">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="heading-2">Dados do Aluno</div>
                        <?php if ((int)$aovivo === 1): ?>
                            <span class="badge-live"><i class="bi bi-broadcast"></i> AULA AO VIVO</span>
                        <?php endif; ?>
                    </div>
                    <p class="form-hint mb-3">
                        <?php if ((int)$aovivo === 1): ?>
                            Participe das transmissões ao vivo e tenha acesso às gravações.
                        <?php else: ?>
                            Acesso imediato ao conteúdo gravado; aulas ao vivo quando programadas.
                        <?php endif; ?>
                    </p>

                    <form id="formInscricao" class="needs-validation" novalidate>
                        <!-- Contexto oculto -->
                        <input type="hidden" name="idCurso" id="idCurso" value="<?= h($enIdCurso) ?>">
                        <input type="hidden" name="idTurma" id="idTurma" value="<?= h($enIdTurma) ?>">
                        <input type="hidden" name="Codigochave" value="<?= h($Codigochave) ?>">
                        <input type="hidden" name="CodigoAfiliado" value="<?= h($CodigoAfiliadoVal) ?>">
                        <input type="hidden" name="utm" id="utm" value="">

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Seu nome completo" required minlength="3" autocomplete="name">
                                    <label for="nome">Nome completo</label>
                                    <div class="invalid-feedback">Informe seu nome (mín. 3 caracteres).</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com" required autocomplete="email" inputmode="email">
                                    <label for="email">E-mail</label>
                                    <div class="invalid-feedback">Digite um e-mail válido.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000" required autocomplete="tel" inputmode="numeric" pattern="^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$">
                                    <label for="telefone">Celular (WhatsApp)</label>
                                    <div class="invalid-feedback">Informe um celular válido (ex.: 85 99999-0000).</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="objetivo" name="objetivo" placeholder="Ex.: INSS, TJ, PF..." minlength="2">
                                    <label for="objetivo">Seu concurso-alvo (opcional)</label>
                                </div>
                            </div>

                            <?php if ($hasAnyTime): ?>
                                <div class="col-12">
                                    <label class="form-label">Escolha seu horário preferido (ao vivo)</label>
                                    <div class="row g-2">
                                        <?php if ($hasManha): ?>
                                            <div class="col-md-4">
                                                <div class="opt-time d-flex align-items-center justify-content-between">
                                                    <label class="me-2 mb-0" for="horario_manha">
                                                        <i class="bi bi-sunrise me-1"></i> Manhã às <?= h(fmtHora($horamanha)) ?>
                                                    </label>
                                                    <!-- value em HH:MM:SS -->
                                                    <input class="form-check-input" type="radio" name="horario" id="horario_manha" value="<?= h($horamanha) ?>">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($hasTarde): ?>
                                            <div class="col-md-4">
                                                <div class="opt-time d-flex align-items-center justify-content-between">
                                                    <label class="me-2 mb-0" for="horario_tarde">
                                                        <i class="bi bi-sunset me-1"></i> Tarde às <?= h(fmtHora($horatarde)) ?>
                                                    </label>
                                                    <input class="form-check-input" type="radio" name="horario" id="horario_tarde" value="<?= h($horatarde) ?>">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($hasNoite): ?>
                                            <div class="col-md-4">
                                                <div class="opt-time d-flex align-items-center justify-content-between">
                                                    <label class="me-2 mb-0" for="horario_noite">
                                                        <i class="bi bi-moon-stars me-1"></i> Noite às <?= h(fmtHora($horanoite)) ?>
                                                    </label>
                                                    <input class="form-check-input" type="radio" name="horario" id="horario_noite" value="<?= h($horanoite) ?>">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text text-white-50">Isso ajuda a organizar sua turma ao vivo. Você poderá mudar depois.</div>
                                </div>
                            <?php endif; ?>

                            <!-- Aceite LGPD (desativado no seu layout, mantendo feedback) -->
                            <div class="col-12">
                                <div class="form-check">
                                    <!-- <input class="form-check-input" type="checkbox" value="1" id="aceite" required>
                                    <label class="form-check-label small" for="aceite">Concordo em receber comunicações sobre minha inscrição e uso da plataforma.</label> -->
                                    <div class="invalid-feedback">Você precisa aceitar para continuar.</div>
                                </div>
                            </div>

                            <div class="col-12 d-grid">
                                <button class="btn btn-cta btn-lg" type="submit">
                                    <i class="bi bi-arrow-right-circle me-2"></i> Continuar
                                </button>
                            </div>

                            <div class="col-12">
                                <p class="small small-muted mb-0">
                                    Ao continuar, você concorda com os <a href="#" class="link-light link-underline-opacity-0">Termos</a> e a
                                    <a href="#" class="link-light link-underline-opacity-0">Política de Privacidade</a>.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Lateral -->
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4 mb-3">
                        <div class="fw-bold mb-2"><i class="bi bi-stars me-2"></i>Você recebe</div>
                        <ul class="small mb-0">
                            <li class="mb-1">Acesso imediato ao conteúdo gravado</li>
                            <li class="mb-1">Aulas ao vivo com o professor</li>
                            <li class="mb-1">Simulados e PDFs para download</li>
                            <li class="mb-1">Certificação digital</li>
                            <li class="mb-1">Suporte direto</li>
                        </ul>
                    </div>
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div class="small text-white-50 mb-1">Plano recomendado</div>
                                <div class="fs-4 fw-bold"><?= h($nomeCurso ?: 'Desenvolvimento Web (PHP)') ?></div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas Limitadas</span>
                        </div>

                        <?php
                        // ------- Preparação de preços -------
                        $precoBase   = isset($valordocurso)   ? (float)$valordocurso   : 0.0;
                        $precoVista  = isset($valoravista)    ? (float)$valoravista    : 0.0;
                        $precoCartao = isset($valornocartao)  ? (float)$valornocartao  : 0.0;

                        $fmt = fn(float $v) => 'R$ ' . number_format($v, 2, ',', '.');
                        $pct = function (float $de, float $para): ?int {
                            if ($de <= 0 || $para <= 0 || $para >= $de) return null;
                            return (int)round((1 - ($para / $de)) * 100);
                        };

                        $offVista  = $pct($precoBase, $precoVista);
                        $offCartao = $pct($precoBase, $precoCartao);
                        ?>

                        <!-- Valor base (riscado) -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Valor do curso</div>
                            <div class="text-white-50" style="text-decoration:line-through;">
                                <?= $precoBase > 0 ? $fmt($precoBase) : '—' ?>
                            </div>
                        </div>

                        <?php if ($precoVista > 0): ?>
                            <!-- Destaque: À vista -->
                            <div class="mt-3 p-3 rounded-3"
                                style="background:linear-gradient(135deg, rgba(84,225,195,.12), rgba(84,225,195,.06)); border:1px solid rgba(84,225,195,.25);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge rounded-pill text-dark" style="background:#54e1c3; font-weight:800;">Melhor preço à vista</span>
                                    <?php if (!is_null($offVista)): ?>
                                        <span class="badge rounded-pill"
                                            style="background:rgba(84,225,195,.15); border:1px solid rgba(84,225,195,.45); color:#54e1c3;">
                                            -<?= $offVista ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex align-items-end gap-2 mt-2">
                                    <div class="display-5 fw-bold" style="color:#00BB9C; line-height:1;">
                                        <?= $fmt($precoVista) ?>
                                    </div>
                                    <div class="small text-white-50 mb-2">à vista no Pix/Boleto</div>
                                </div>
                            </div>

                            <!-- Cartão -->
                            <div class="mt-3 p-3 rounded-3"
                                style="background:linear-gradient(135deg, rgba(255,156,0,.08), rgba(255,156,0,.03)); border:1px solid rgba(255,156,0,.25);">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <div class="small text-white-50">No cartão de crédito</div>
                                    <?php if (!is_null($offCartao)): ?>
                                        <span class="badge rounded-pill"
                                            style="background:rgba(255,156,0,.15); border:1px solid rgba(255,156,0,.45); color:#FF9C00;">
                                            -<?= $offCartao ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="fs-4 fw-semibold" style="color:#FFB64D; line-height:1;">
                                    <?= $precoCartao > 0 ? $fmt($precoCartao) : 'Consulte' ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="small text-white-50 mt-2">Vitalício com atualizações</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== SEGURANÇA / SUPORTE ===================== -->
    <section id="seguranca">
        <div class="container">
            <div class="row gy-4 align-items-start">
                <div class="col-lg-7" data-aos="fade-up">
                    <div class="heading-2 mb-2">Segurança e Suporte</div>
                    <p class="mb-2">Seus dados são utilizados exclusivamente para criar seu acesso e comunicar informações sobre suas aulas e pagamentos. Você pode solicitar a remoção a qualquer momento.</p>
                    <ul class="small mb-0">
                        <li class="mb-1"><i class="bi bi-check2-circle me-2" style="color:#54e1c3"></i>Ambiente seguro e criptografado</li>
                        <li class="mb-1"><i class="bi bi-check2-circle me-2" style="color:#54e1c3"></i>Conformidade com boas práticas de privacidade</li>
                        <li class="mb-1"><i class="bi bi-check2-circle me-2" style="color:#54e1c3"></i>Atendimento pelo WhatsApp e e-mail</li>
                    </ul>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="fw-bold mb-2"><i class="bi bi-whatsapp me-2"></i>Dúvidas?</div>
                        <p class="small small-muted mb-3">Fale diretamente com o professor para orientação rápida.</p>
                        <a class="btn btn-outline-light w-100" target="_blank" rel="noopener" href="<?= h($linkwhatsapp) ?> *<?= h($nomeCurso ?: 'Excel para Concursos') ?>*">
                            Chamar no WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== RODAPÉ ===================== -->
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

        // ---------------- Overlay de carregamento ----------------
        let _loaderEl = null;

        function showLoader(msg = 'Processando sua inscrição com segurança...') {
            if (_loaderEl) return;
            _loaderEl = document.createElement('div');
            _loaderEl.id = 'overlayLoader';
            _loaderEl.innerHTML = `
        <div style="
            position:fixed; inset:0; backdrop-filter: blur(4px);
            background: rgba(17,34,64,.75); display:flex; align-items:center; justify-content:center; z-index:9999;
        ">
          <div style="
            width:min(92vw,520px); background:#0d1a34; color:#e9f3ff;
            border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:24px; text-align:center;
            box-shadow:0 20px 60px rgba(0,0,0,.45);
          ">
            <div class="mb-2" style="font-weight:800; font-size:1.1rem;">
              <i class="bi bi-shield-lock me-2"></i>Conexão segura
            </div>
            <div class="d-flex align-items-center justify-content-center mb-3" style="gap:.75rem;">
              <div class="spinner-border" role="status" aria-hidden="true"></div>
              <div style="opacity:.9;">${msg}</div>
            </div>
            <div class="small" style="color:#9fb1d1">
              Seus dados são criptografados. Não armazenamos informações sensíveis do cartão neste site.
            </div>
          </div>
        </div>`;
            document.body.appendChild(_loaderEl);
        }

        function hideLoader() {
            if (_loaderEl) {
                _loaderEl.remove();
                _loaderEl = null;
            }
        }

        // -------- Persistência local --------
        const FIELDS = ['nome', 'email', 'telefone', 'objetivo', 'horario'];

        function loadFromStorage() {
            FIELDS.forEach(id => {
                const v = localStorage.getItem('insc_' + id);
                if (!v) return;
                const el = document.getElementById(id);
                if (el) el.value = v;
                if (id === 'horario') {
                    const r = document.querySelector(`input[name="horario"][value="${v}"]`);
                    if (r) r.checked = true;
                }
            });
        }

        function saveToStorage() {
            FIELDS.forEach(id => {
                const el = document.getElementById(id);
                if (el) localStorage.setItem('insc_' + id, el.value.trim());
            });
            const r = document.querySelector('input[name="horario"]:checked');
            if (r) localStorage.setItem('insc_horario', r.value);
        }
        loadFromStorage();
        FIELDS.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', saveToStorage);
        });
        document.querySelectorAll('input[name="horario"]').forEach(r => {
            r.addEventListener('change', () => localStorage.setItem('insc_horario', r.value));
        });

        // -------- Validação + AJAX --------
        const form = document.getElementById('formInscricao');
        form.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            ev.stopPropagation();

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            // Se houver horários disponíveis, exigir seleção
            const radios = document.querySelectorAll('input[name="horario"]');
            let horarioSel = '';
            if (radios.length > 0) {
                const r = document.querySelector('input[name="horario"]:checked');
                if (!r) {
                    alert('Selecione seu horário preferido.');
                    return;
                }
                horarioSel = r.value; // HH:MM:SS
                localStorage.setItem('insc_horario', horarioSel);
            }

            saveToStorage();

            const aceiteEl = document.getElementById('aceite');
            const data = {
                idCurso: document.getElementById('idCurso').value,
                idTurma: document.getElementById('idTurma').value,
                Codigochave: (document.querySelector('input[name="Codigochave"]')?.value || ''),
                CodigoAfiliado: (document.querySelector('input[name="CodigoAfiliado"]')?.value || ''),
                nome: document.getElementById('nome').value.trim(),
                email: document.getElementById('email').value.trim(),
                telefone: document.getElementById('telefone').value.trim(),
                objetivo: document.getElementById('objetivo').value.trim(),
                horario: horarioSel,
                aceite: (aceiteEl && aceiteEl.checked) ? 1 : 0,
                utm: document.getElementById('utm').value
            };

            showLoader('Salvando seus dados e preparando a próxima etapa...');

            const controller = new AbortController();
            const to = setTimeout(() => controller.abort(), 20000);

            try {
                const resp = await fetch('vendasv1.0/ajax_inscricaocurso.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data),
                    signal: controller.signal
                });
                clearTimeout(to);

                if (!resp.ok) throw new Error('Falha de comunicação. Código ' + resp.status);
                const res = await resp.json();

                if (res && (res.ok === true || res.success === true)) {
                    if (res.idCurso) localStorage.setItem('insc_idCurso', res.idCurso);
                    if (res.idTurma) localStorage.setItem('insc_idTurma', res.idTurma);
                    if (res.lead) localStorage.setItem('insc_lead', String(res.lead));
                    if (res.horario) localStorage.setItem('insc_horario', res.horario);

                    const redirect = res.redirect || 'vendas_plano.php';
                    const qs = new URLSearchParams({
                        lead: res.lead ? String(res.lead) : '',
                        idCurso: data.idCurso,
                        idTurma: data.idTurma,
                        nome: data.nome,
                        email: data.email,
                        telefone: data.telefone,
                        objetivo: data.objetivo,
                        horario: data.horario,
                        utm: data.utm
                    }).toString();

                    window.location.href = redirect + (qs ? ('?' + qs) : '');
                } else {
                    hideLoader();
                    alert((res && (res.msg || res.message)) || 'Não foi possível registrar sua inscrição. Tente novamente.');
                }
            } catch (err) {
                hideLoader();
                alert('Não foi possível concluir a solicitação agora. Verifique sua conexão e tente novamente.\n\n' + (err && err.message ? err.message : ''));
            }
        });

        // UTM da URL -> hidden
        const p = new URLSearchParams(location.search);
        const utm = p.get('utm') || '';
        if (utm) document.getElementById('utm').value = utm;
    </script>
</body>

</html>