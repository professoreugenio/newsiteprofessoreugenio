<?php
define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php';

// Includes principais
require_once 'config_default1.0/query_dados.php';
require_once 'config_curso1.0/query_curso.php';

// Redireciona se não houver turma
if (empty($idTurma)) {
    header('Location: turmas.php');
    exit;
}

// Continua carregando dados
require_once 'config_default1.0/query_turma.php';
require_once 'config_curso1.0/query_publicacoes2.0.php';
require_once 'config_curso1.0/query_anexos.php';

// Redireciona se o tipo de curso for inválido
if (isset($tipocurso) && $tipocurso == 0) {
    header('Location: ../curso/modulos.php');
    exit;
}

/* =========================================================
   CONTAGENS DE ASSISTIDAS x NÃO ASSISTIDAS
   ========================================================= */
$totalAssistidas   = 0;
$totalNaoAssistidas = 0;

if (!empty($fetchTodasLicoes)) {
    $stmtCheck = $con->prepare("
        SELECT 1 
        FROM a_aluno_andamento_aula 
        WHERE idpublicaa = :codigoaula 
          AND idalunoaa  = :codigousuario 
          AND idcursoaa  = :idcurso 
        LIMIT 1
    ");

    foreach ($fetchTodasLicoes as $value) {
        $stmtCheck->bindParam(":codigoaula", $value['codigopublicacoes']);
        $stmtCheck->bindParam(":codigousuario", $codigousuario);
        $stmtCheck->bindParam(":idcurso", $codigocurso);
        $stmtCheck->execute();
        if ($stmtCheck->fetch(PDO::FETCH_NUM)) $totalAssistidas++;
        else $totalNaoAssistidas++;
    }
}

/* =========================================================
   MAPA: codigopublicacoes -> dados úteis da lição
   ========================================================= */
$mapLicoes = []; // [codigopublicacoes] => ['ordem'=>..., 'titulo'=>..., 'idpubOriginal'=>..., 'liberada'=>...]
foreach ($fetchTodasLicoes as $a) {
    $mapLicoes[(string)$a['codigopublicacoes']] = [
        'ordem'         => $a['ordempc'] ?? null,
        'titulo'        => $a['titulo'] ?? '',
        'idpubOriginal' => $a['idpublicacaopc'] ?? null,
        'liberada'      => $a['aulaliberadapc'] ?? null,
    ];
}

/* =========================================================
   ÚLTIMAS 4 AULAS ASSISTIDAS
   Ordena por data/hora quando possível; fallback pelo id do andamento
   ========================================================= */
$sqlUltimas = "
    SELECT 
        aa.idpublicaa,
        MAX(
            COALESCE(
                STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'),
                NULL
            )
        ) AS ultimo_dt,
        MAX(aa.codigoandamento) AS ultimo_id
    FROM a_aluno_andamento_aula aa
    WHERE aa.idalunoaa = :aluno AND aa.idcursoaa = :curso
    GROUP BY aa.idpublicaa
    ORDER BY 
        CASE 
            WHEN MAX(COALESCE(STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'), NULL)) IS NOT NULL 
                THEN MAX(COALESCE(STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'), NULL))
            ELSE MAX(aa.codigoandamento)
        END DESC
    LIMIT 4
";
$stmtUlt = $con->prepare($sqlUltimas);
$stmtUlt->bindParam(":aluno", $codigousuario);
$stmtUlt->bindParam(":curso", $codigocurso);
$stmtUlt->execute();
$ultimasAssistidas = $stmtUlt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   Utilitário data BR
   ========================================================= */
function formatarDataBR($dtStr)
{
    if (!empty($dtStr)) {
        $ts = strtotime($dtStr);
        if ($ts) return date('d/m/Y H:i', $ts);
    }
    return '—';
}

/* =========================================================
   Navbar: nome/foto do aluno (fallback seguros)
   ========================================================= */
$__nomeAluno = $nome ?? ($nomeUser ?? 'Aluno');
$__fotoAluno = 'https://professoreugenio.com/fotos/usuarios/usuario.png';
if (!empty($pastasc ?? null) && !empty($imagem50 ?? null) && $imagem50 !== 'usuario.jpg') {
    $__fotoAluno = 'https://professoreugenio.com/fotos/usuarios/' . $pastasc . '/' . $imagem50;
}

/* =========================================================
   Progresso / totais para o cabeçalho
   ========================================================= */
$totalLicoesModulo = is_array($fetchTodasLicoes) ? count($fetchTodasLicoes) : 0;
// $perc e $corBarra já vêm dos seus queries; mantidos como fonte da verdade
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página de Módulos — Professor Eugênio</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --brand-h1: #00BB9C;
            --brand-h2: #FF9C00;
            --brand-bg: #112240;
            --brand-text: #ffffff;

            --card-bg: rgba(255, 255, 255, .04);
            --card-bd: rgba(255, 255, 255, .08);

            --amber: #FF9C00;
            --emerald: #00BB9C;
            --indigo: #4F46E5;
            --rose: #E11D48;
            --slate: #94a3b8;
        }

        * {
            scroll-margin-top: 96px
        }

        body {
            background: var(--brand-bg);
            color: var(--brand-text)
        }

        .navbar {
            background: #0f1d36
        }

        .navbar .nav-link,
        .navbar .navbar-brand {
            color: #fff !important
        }

        .navbar .nav-link:hover {
            opacity: .85
        }

        .navbar.navbar-dark .navbar-toggler {
            border-color: rgba(255, 255, 255, .2)
        }

        .navbar.navbar-dark .navbar-toggler-icon {
            filter: invert(1)
        }

        h1,
        .h1 {
            color: var(--brand-h1)
        }

        h2,
        .h2 {
            color: var(--brand-h2)
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-bd);
            backdrop-filter: blur(2px);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            border-color: rgba(255, 255, 255, .16);
        }

        .card-colored {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: #076379;
        }

        .card-colored::before {
            content: "";
            position: absolute;
            inset: -1px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, 0));
            z-index: -1;
        }

        .lesson-number {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
        }

        .ln-amber {
            background: linear-gradient(135deg, #7a4300, var(--amber));
        }

        .ln-emerald {
            background: linear-gradient(135deg, #064e3b, var(--emerald));
        }

        .ln-indigo {
            background: linear-gradient(135deg, #1e1b4b, var(--indigo));
        }

        .ln-rose {
            background: linear-gradient(135deg, #7f1d1d, var(--rose));
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #e2e8f0;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .6rem;
            border-radius: 999px;
            font-size: .8rem;
            line-height: 1;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(0, 0, 0, .2);
        }

        .chip i {
            opacity: .9
        }

        .list-group-item {
            background: rgba(255, 255, 255, .03);
            border-color: rgba(255, 255, 255, .08);
            color: #e5e7eb;
            display: flex;
            align-items: center;
        }

        .lg-stripe {
            width: 4px;
            align-self: stretch;
            border-radius: 4px;
            margin-right: .75rem;
            opacity: .9;
        }

        .stripe-done {
            background: var(--emerald);
        }

        .stripe-pending {
            background: var(--slate);
        }

        .stripe-late {
            background: var(--rose);
        }

        .btn-cta {
            background: var(--brand-h2);
            color: #111;
            border: 0;
            font-weight: 700
        }

        .btn-cta:hover {
            filter: brightness(.95)
        }

        .btn-amber {
            background: var(--amber);
            color: #111;
            border: 0
        }

        .btn-amber:hover {
            filter: brightness(.95)
        }

        .btn-emerald {
            background: var(--emerald);
            color: #0b1c17;
            border: 0
        }

        .btn-emerald:hover {
            filter: brightness(.95)
        }

        .btn-indigo {
            background: var(--indigo);
            color: #fff;
            border: 0
        }

        .btn-indigo:hover {
            filter: brightness(1.05)
        }

        .btn-outline-light {
            --bs-btn-color: #e5e7eb;
            --bs-btn-border-color: #e5e7eb;
            --bs-btn-hover-bg: #e5e7eb;
            --bs-btn-hover-color: #0f172a;
            --bs-btn-hover-border-color: #e5e7eb;
        }

        .progress {
            background: rgba(255, 255, 255, .08);
            height: 10px
        }

        .progress-bar {
            background: var(--brand-h1)
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .2)
        }

        .section-gap {
            padding-top: 24px;
            padding-bottom: 24px
        }

        .text-muted-2 {
            color: #cbd5e1 !important
        }

        .shadow-soft {
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25)
        }

        .rounded-4 {
            border-radius: 1rem !important
        }
    </style>
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
</head>

<body>
    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <!-- NAVBAR -->
    <!-- <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="<?= $__fotoAluno; ?>" alt="Foto do aluno" class="avatar">
                <span class="fw-semibold"><?= htmlspecialchars($__nomeAluno); ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nv"
                aria-controls="nv" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="nv" class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-mortarboard me-1"></i>Curso</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-chat-dots me-1"></i>Contato</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="#"><i class="bi bi-x-circle me-1"></i>Fechar</a></li>
                </ul>
            </div>
        </div>
    </nav> -->

    <!-- CABEÇALHO / TÍTULO DO MÓDULO -->
    <header class="container section-gap">
        <div class="row align-items-center g-3">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="h3 mb-2"><?= htmlspecialchars($nmmodulo ?? 'Módulo'); ?></h1>

                <div class="d-flex align-items-center gap-3">
                    <!-- Progresso -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Progresso do módulo</span>
                            <span><strong id="pctLabel"><?= (int)$perc; ?>%</strong></span>
                        </div>
                        <?php
                        $percInt = (int)$perc;

                        // Define a cor da barra conforme o percentual
                        if ($percInt < 20) {
                            $progressColor = 'bg-danger'; // vermelho
                        } elseif ($percInt < 50) {
                            $progressColor = 'bg-warning'; // laranja
                        } elseif ($percInt < 80) {
                            $progressColor = 'bg-primary'; // azul
                        } else {
                            $progressColor = 'bg-success'; // verde
                        }
                        ?>


                        <div class="progress rounded-pill">
                            <div class="progress-bar <?= $progressColor; ?>" id="pctBar" role="progressbar"
                                style="width: <?= $percInt; ?>%"
                                aria-valuenow="<?= $percInt; ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>

                    </div>

                    <!-- Totais -->
                    <span class="chip">
                        <i class="bi bi-collection-play"></i>
                        <?= (int)$totalAssistidas; ?> de <?= (int)$totalLicoesModulo; ?> lições
                    </span>
                </div>

                <!-- Contadores “AULAS / posição no módulo” (teu require com ajustes visuais) -->
                <div class="mt-3">
                    <?php require 'config_curso1.0/require_CountAulas.php'; ?>
                </div>
            </div>

            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="./modulos.php" class="btn btn-emerald rounded-3 shadow-soft">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Ver todos os módulos
                </a>
            </div>
        </div>
    </header>

    <!-- PRIMEIRAS 4 / ÚLTIMAS 4 EM CARDS -->
    <section class="container section-gap">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 m-0"><?= empty($ultimasAssistidas) ? 'Comece por aqui' : 'Continue de onde parou'; ?></h2>
            <a href="#lista-licoes" class="text-decoration-none small">Ver lista completa <i class="bi bi-arrow-down-short"></i></a>
        </div>

        <div class="row g-3 justify-content-center">
            <?php if (empty($ultimasAssistidas)): ?>
                <?php
                // Ordena por ordempc e pega as 4 primeiras lições do módulo
                $licoesOrdenadas = $fetchTodasLicoes;
                usort($licoesOrdenadas, function ($a, $b) {
                    return (int)($a['ordempc'] ?? 0) <=> (int)($b['ordempc'] ?? 0);
                });
                $primeirasLicoes = array_slice($licoesOrdenadas, 0, 4);
                ?>

                <?php if (empty($primeirasLicoes)): ?>
                    <div class="col-12">
                        <div class="alert alert-secondary border-0">
                            Nenhuma lição encontrada neste módulo.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($primeirasLicoes as $a):
                        $ordem  = $a['ordempc'] ?? null;
                        $titulo = $a['titulo'] ?? '';
                        $idOrig = $a['idpublicacaopc'] ?? null;
                        $lib    = $a['aulaliberadapc'] ?? '0';

                        $encAula = encrypt($idOrig, 'e');

                        // Escolha estética da “cor” do número
                        $badgeClass = 'ln-amber';
                        if ($lib === '1') $badgeClass = 'ln-emerald';
                    ?>
                        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
                            <div class="card card-colored h-100 rounded-4 p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="lesson-number <?= $badgeClass; ?>">
                                        <?= htmlspecialchars(str_pad((string)($ordem ?? 0), 2, '0', STR_PAD_LEFT)); ?>
                                    </div>
                                    <span class="badge <?= $lib == '1' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $lib == '1' ? '<i class="bi bi-unlock-fill"></i> Liberada' : '<i class="bi bi-lock-fill"></i> Bloqueada' ?>
                                    </span>
                                </div>

                                <h3 class="h6 mt-3 mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
                                    <?= htmlspecialchars($titulo) ?>
                                </h3>
                                <p class="small text-muted-2 mb-3"><i class="bi bi-stars me-1"></i> Comece por aqui</p>

                                <div class="d-grid gap-2 mt-auto">
                                    <?php if ($lib == '1'): ?>
                                        <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-amber btn-sm">
                                            <i class="bi bi-play-circle me-1"></i> Ir para a aula
                                        </a>
                                    <?php else: ?>
                                        <span class="btn disabled btn-outline-light btn-sm">
                                            <i class="bi bi-ban me-1"></i> Aula bloqueada
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php else: ?>
                <?php foreach ($ultimasAssistidas as $row):
                    $codPub = (string)$row['idpublicaa']; // corresponde a codigopublicacoes
                    if (!isset($mapLicoes[$codPub])) continue;

                    $ordem   = $mapLicoes[$codPub]['ordem'];
                    $titulo  = $mapLicoes[$codPub]['titulo'];
                    $idOrig  = $mapLicoes[$codPub]['idpubOriginal'];
                    $lib     = $mapLicoes[$codPub]['liberada'];
                    $encAula = encrypt($idOrig, 'e');

                    $ultimoAcesso = formatarDataBR($row['ultimo_dt']);

                    // estética do número
                    $badgeClass = 'ln-emerald'; // assistida -> verde
                ?>
                    <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
                        <div class="card card-colored h-100 rounded-4 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="lesson-number <?= $badgeClass; ?>">
                                    <?= htmlspecialchars(str_pad((string)($ordem ?? 0), 2, '0', STR_PAD_LEFT)); ?>
                                </div>
                                <span class="badge bg-success">
                                    <i class="bi bi-check2-circle"></i> Acessada
                                </span>
                            </div>

                            <h3 class="h6 mt-3 mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
                                <?= htmlspecialchars($titulo) ?>
                            </h3>

                            <div class="small text-muted-2 mb-3">
                                <i class="bi bi-clock-history me-1"></i> Último acesso:
                                <strong><?= $ultimoAcesso; ?></strong>
                            </div>

                            <div class="d-grid gap-2 mt-auto">
                                <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-indigo btn-sm">
                                    <i class="bi bi-play-circle me-1"></i> Ir para a aula
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- LISTA COMPLETA DE LIÇÕES -->
    <section class="container section-gap" id="lista-licoes">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 m-0">Todas as lições do módulo</h2>
            <div class="d-flex gap-2">
                <span class="chip"><i class="bi bi-check2-circle"></i> Assistidas: <?= (int)$totalAssistidas; ?></span>
                <span class="chip"><i class="bi bi-dot"></i> Pendentes: <?= (int)$totalNaoAssistidas; ?></span>
            </div>
        </div>

        <div class="list-group rounded-4 overflow-hidden">
            <?php
            // Preparar um statement para verificar se cada lição foi assistida
            $stmtOne = $con->prepare("
            SELECT 1 FROM a_aluno_andamento_aula 
            WHERE idpublicaa = :pub AND idalunoaa = :aluno AND idcursoaa = :curso 
            LIMIT 1
        ");

            // Ordena $fetchTodasLicoes por ordempc para a listagem completa
            $todasOrdenadas = $fetchTodasLicoes;
            usort($todasOrdenadas, function ($a, $b) {
                return (int)($a['ordempc'] ?? 0) <=> (int)($b['ordempc'] ?? 0);
            });

            foreach ($todasOrdenadas as $lx):
                $ordem  = $lx['ordempc'] ?? null;
                $titulo = $lx['titulo'] ?? '';
                $pubId  = $lx['codigopublicacoes'] ?? null;
                $lib    = $lx['aulaliberadapc'] ?? '0';
                $idOrig = $lx['idpublicacaopc'] ?? null;

                // status assistida?
                $assistida = false;
                if ($pubId) {
                    $stmtOne->bindParam(':pub', $pubId);
                    $stmtOne->bindParam(':aluno', $codigousuario);
                    $stmtOne->bindParam(':curso', $codigocurso);
                    $stmtOne->execute();
                    if ($stmtOne->fetch(PDO::FETCH_NUM)) $assistida = true;
                }

                // stripe e ícone
                $stripeClass = $assistida ? 'stripe-done' : 'stripe-pending';
                $iconRight   = $assistida ? '<i class="bi bi-check-circle ms-3 text-success"></i>' : '<i class="bi bi-circle ms-3 text-warning"></i>';

                // cor do número
                $ln = $assistida ? 'ln-emerald' : 'ln-amber';

                $encAula = encrypt($idOrig, 'e');
            ?>
                <a href="<?= $lib == '1' ? 'actionCurso.php?lc=' . $encAula : '#'; ?>"
                    class="list-group-item list-group-item-action <?= $lib == '1' ? '' : 'disabled'; ?>"
                    title="<?= $lib == '1' ? 'Abrir aula' : 'Aula bloqueada'; ?>">
                    <span class="lg-stripe <?= $stripeClass; ?>"></span>
                    <span class="lesson-number me-3 <?= $ln; ?>">
                        <?= htmlspecialchars(str_pad((string)($ordem ?? 0), 2, '0', STR_PAD_LEFT)); ?>
                    </span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong class="text-white text-truncate"><?= htmlspecialchars($titulo); ?></strong>
                            <small class="text-muted-2">
                                <?= $assistida ? 'Status: ' . '<span class="text-success fw-semibold">Assistida</span>'
                                    : 'Status: ' . '<span class="text-warning fw-semibold">Pendente</span>'; ?>
                            </small>
                        </div>
                        <?php if ($lib !== '1'): ?>
                            <small class="text-muted-2"><i class="bi bi-lock-fill me-1"></i> Aula bloqueada</small>
                        <?php endif; ?>
                    </div>
                    <?= $iconRight; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- RODAPÉ -->
    <footer class="container section-gap">
        <div class="text-center small text-muted-2">
            © <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            duration: 700,
            easing: 'ease-out-cubic'
        });
        document.getElementById('ano').textContent = new Date().getFullYear();
        // Progresso vindo do backend já aplicado na barra/label
    </script>

</body>

</html>