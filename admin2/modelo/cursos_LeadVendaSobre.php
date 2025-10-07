<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/QueryCursos.php'; ?>

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <style>
        .note-editor.note-frame {
            border-radius: 1rem;
        }

        .note-editable {
            min-height: 220px;
        }
    </style>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> CURSO <?= $Nomecurso ?></h3>
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>

            </div>

            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavSecundarioCurso.php'; ?>

            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavCursosEditar.php'; ?>
        </div>
        <!-- CORPO PÁGINA -->
        <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/BodyCursoFormSobre.php'; ?>
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

    <script src="cursosv1.0/JS_cursoLeadEditarSobre.js?<?= time(); ?>"></script>


    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainer">
    </div>
    <!-- Popper e Bootstrap -->
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>