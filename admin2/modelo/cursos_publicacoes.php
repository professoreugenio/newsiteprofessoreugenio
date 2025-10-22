<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (empty($filtro)): $filtro = '0';
endif;
?>
<?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/QueryCursos.php'; ?>
<?php require_once APP_ROOT . '/admin2/modelo/modulosv1.0/QueryModulos.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- Summernote CSS -->
    <!-- jQuery (deve vir antes de tudo) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <?php
    function extrairIdYoutube($url)
    {
        // Expressões para suportar vários formatos de link do YouTube
        if (preg_match('/(?:youtube\.com\/.*v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
    $videoID = extrairIdYoutube($Videoyoutube);
    ?>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h5><i class="bi bi-journal-text me-2"></i> Curso <?= $Nomecurso ?> Com:<?= $comercial  ?></h5>
            </div>
            <div class="mt-4">
                <?php require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/SubnavSecundarioPublicacoes.php'; ?>
            </div>
            <div class="mt-4">
                <?php require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/Subnav.php'; ?>
            </div>
        </div>
        <!-- <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h4 style="color: #0080c0;"><i class="bi bi-journal-text me-2"></i> CURSO <?= $Nomecurso ?> </h4>

                <small><?= diadasemana($data, 2); ?></small>

            </div>

            <?php // require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavCursosEditar.php'; ?>
            
            <?php // require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/SubnavSecundarioPublicacoes.php'; ?>

        </div> -->
        <!-- CORPO PÁGINA -->
        <div class="mb-3 d-flex gap-2">
            <a href="conteudoCategorias.php?filtro=visivel" class="btn btn-outline-success btn-sm <?= $filtro == 'visivel' ? 'active' : '' ?>"><i class="bi bi-eye"></i> Visíveis</a>
            <a href="conteudoCategorias.php?filtro=oculto" class="btn btn-outline-secondary btn-sm <?= $filtro == 'oculto' ? 'active' : '' ?>"><i class="bi bi-eye-slash"></i> Ocultas</a>
            <a href="conteudoCategorias.php?filtro=lixeira" class="btn btn-outline-danger  btn-sm <?= $filtro == 'lixeira' ? 'active' : '' ?>"><i class="bi bi-trash"></i> Lixeira</a>
            <a href="conteudoCategorias.php?filtro=copias" class="btn btn-outline-default  btn-sm <?= $filtro == 'copias' ? 'active' : '' ?>"><i class="bi bi-files"></i> Copias</a>
        </div>
        <?php if ($matriz == 1): ?>
            <small>Originais</small>
            <?php require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/v1.BodyPublicacoesLista.php'; ?>
        <?php else: ?>
            <small>Cópias</small>
            <?php require 'publicacoesv1.0/v4.BodyPublicacoesLista.php'; ?>

        <?php endif; ?>
    </div>
    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>
    <script>
        function alternarStatus(idCurso) {
            // Simulação de ação
            alert('Alternar status do curso ID: ' + idCurso);
            // Aqui você pode usar AJAX para atualizar no banco sem recarregar a página
        }
    </script>

    <script src="cursosv1.0/JS_cursoEditarExcluir.js?<?= time(); ?>"></script>


    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainer">
    </div>
    <!-- Popper e Bootstrap -->
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>