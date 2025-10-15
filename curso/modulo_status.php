<?php
define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php';

require_once 'config_default1.0/query_dados.php';
require_once 'config_curso1.0/query_curso.php';
require_once 'config_curso1.0/query_publicacoes2.0.php';
require_once 'config_curso1.0/query_anexos.php';
if (empty($idTurma)) {
    echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
    exit();
}
require 'config_default1.0/query_turma.php';


// Redireciona se o tipo de curso for inválido
if (isset($tipocurso) && $tipocurso == 0) {
    header('Location: ../curso/modulos.php');
    exit;
}


// Consulta apenas o campo necessário
$sql = "SELECT dataprazosi 
        FROM new_sistema_inscricao_PJA 
        WHERE chaveturma = :chaveturma 
          AND codigousuario = :codigousuario 
        LIMIT 1";

$stmt = $con->prepare($sql);
$stmt->bindParam(':chaveturma', $chaveturmaUser, PDO::PARAM_STR);
$stmt->bindParam(':codigousuario', $codigoUser, PDO::PARAM_INT);
$stmt->execute();

$rw = $stmt->fetch(PDO::FETCH_ASSOC);
$dataprazo = $rw['dataprazosi'] ?? '';



if (!empty($dataprazo)) {
    $dataAtual = date('Y-m-d');

    if ($dataprazo < $dataAtual) {
        header("Location: suporte.php");
    }
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
$test = '';
$sqlUltimas = "
    SELECT 
        aa.idpublicaa,
        aa.dataaa,
        aa.horaaa,
        aa.codigoandamento
    FROM a_aluno_andamento_aula aa
    WHERE aa.idalunoaa = :aluno 
      AND aa.idcursoaa = :curso
      AND aa.idturmaaa = :turma
      AND aa.idmoduloaa = :modulo
    ORDER BY aa.dataaa DESC, aa.horaaa DESC
    LIMIT 4
";

$stmtUlt = $con->prepare($sqlUltimas);
$stmtUlt->bindParam(":aluno", $codigousuario, PDO::PARAM_INT);
$stmtUlt->bindParam(":curso", $codigocurso, PDO::PARAM_INT);
$stmtUlt->bindParam(":turma", $codigoturma, PDO::PARAM_INT);
$stmtUlt->bindParam(":modulo", $codigomodulo, PDO::PARAM_INT);
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

    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="v2.0/config_css/config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">



    </script>

    <!-- Bootstrap 5 (se já carrega, mantenha o seu) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (se já carrega, ignore) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">


    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_modulo_status.css?<?php echo time(); ?>">
</head>

<body>
    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php include 'v2.0/nav.php'; ?>


    <!-- CABEÇALHO / TÍTULO DO MÓDULO -->
    <!-- <div style="height:70px"></div> -->
    <header class="container section-gap">
        <?php echo $dec = encrypt("RA9db50a600b6a348067f22f46", $action = 'd'); ?>
        <div class="row align-items-center g-3">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="h3 mb-2"><?= htmlspecialchars($nmmodulo ?? 'Módulo'); ?></h1>
                <?php if ($perc > '100') :
                    $perc = '100';
                endif; ?>
                <div class="d-flex align-items-center gap-3">
                    <!-- Progresso -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Progresso do módulo* <?php echo $dataprazo; ?></span>
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
                <div class="mt-1">
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

        <?php require 'config_curso1.0/require_buscar.php'; ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 m-0"><?= empty($ultimasAssistidas) ? 'Comece por aqui' : 'Continue de onde parou' . $test; ?> ou acesse <a href="#lista-licoes" class="text-decoration-none small btn btn-warning">Ver lista completa <i class="bi bi-arrow-down-short"></i></a></h2>

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
                        if ($lib === '1' && $comercialDados === '1') $badgeClass = 'ln-emerald';

                    ?>
                        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up">
                            <div class="card card-colored h-100 rounded-4 p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="lesson-number <?= $badgeClass; ?>">
                                        <?= htmlspecialchars(str_pad((string)($ordem ?? 0), 2, '0', STR_PAD_LEFT)); ?>
                                    </div>
                                    <span class="badge <?= $lib == '1' && $comercialDados === '1' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= '<i class="bi bi-unlock-fill"></i> Liberada' ?>
                                    </span>
                                </div>

                                <h3 class="h6 mt-1 mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
                                    <?= htmlspecialchars($titulo) ?>
                                </h3>
                                <p class="small text-muted-2 mb-3"><i class="bi bi-stars me-1"></i> Comece por aqui</p>

                                <div class="d-grid gap-2 mt-auto">
                                    <?php if ($lib == '1'): ?>
                                        <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-amber btn-sm">
                                            <i class="bi bi-play-circle me-1"></i> Ir para a aula *
                                        </a>
                                    <?php else: ?>

                                        <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-amber btn-sm">
                                            <i class="bi bi-play-circle me-1"></i> Ir para a aula **
                                        </a>

                                        <?php if ($codigoUser == 1): ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- <span class="btn disabled btn-outline-light btn-sm">
                                            <i class="bi bi-ban me-1"></i> Aula bloqueada**
                                        </span> -->
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

                    $ultimoAcesso = formatarDataBR($row['dataaa']);

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

                            <h3 class="h6 mt-1 mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
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

                <?php
                // Pode ver tudo se comercial liberar OU se for admin
                $canSeeAll = (isset($comercialDados) && $comercialDados == '1') || ((int)($codigoUser ?? 0) === 1);

                // Renderiza somente se:
                // - Pode ver tudo (comercial/admin) OU
                // - A lição está liberada ($lib == '1')
                $shouldRender = $canSeeAll || ($lib == '1');

                // Se não deve renderizar, pula este item
                if (!$shouldRender) {
                    continue;
                }

                $wasLocked = ($lib !== '1'); // estava bloqueada originalmente?
                $href  = "actionCurso.php?lc={$encAula}";
                $title = "Abrir aula";
                ?>

                <a href="<?= $href; ?>"
                    class="list-group-item list-group-item-action"
                    title="<?= $title; ?>">
                    <span class="lg-stripe <?= $stripeClass; ?>"></span>

                    <span class="lesson-number me-3 <?= $ln; ?>">
                        <?= htmlspecialchars(str_pad((string)($ordem ?? 0), 2, '0', STR_PAD_LEFT)); ?>
                    </span>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-white text-truncate"><?= htmlspecialchars($titulo); ?></strong>
                            <small class="text-muted-2">
                                <?= $assistida
                                    ? 'Status: <span class="text-success fw-semibold">Assistida</span>'
                                    : 'Status: <span class="text-warning fw-semibold">Pendente</span>'; ?>
                            </small>
                        </div>

                        <?php if ($wasLocked && (int)($codigoUser ?? 0) === 1): ?>
                            <!-- Exibida por privilégio de ADMIN -->
                            <small class="text-muted-2"><i class="bi bi-shield-lock me-1"></i> Exibida por perfil administrador</small>
                        <?php elseif ($wasLocked && isset($comercialDados) && $comercialDados == '1'): ?>
                            <!-- Exibida porque comercial liberou visão total -->
                            <!-- <small class="text-muted-2">
                                <i class="bi bi-star-fill me-1"></i> 
                                </small> -->
                        <?php endif; ?>
                    </div>

                    <?= $iconRight; ?>
                </a>




            <?php endforeach; ?>
        </div>
    </section>
    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>
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

    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>

    <script src="config_turmas1.0/JS_accessturma.js"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
</body>

</html>