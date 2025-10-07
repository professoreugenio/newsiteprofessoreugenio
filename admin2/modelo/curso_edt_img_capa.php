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

</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> Editar <?= $Nomecurso ?></h3>
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>
            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavSecundarioCurso.php'; ?>


            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavCursosEditar.php'; ?>
        </div>


        <!-- Formulário para envio da imagem de capa -->


        <div class="row mb-4">
            <!-- Coluna: Formulário -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="fa fa-camera" aria-hidden="true"></i></i> Foto Capa
                    </div>
                    <div class="card-body">
                        <form id="formImagemCapa" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="idCurso" id="idCursoHidden" value="<?= (int)$_GET['id']; ?>">
                            <input type="hidden" name="pasta" value="<?= $Pasta; ?>">
                            <input type="hidden" id="tipo" name="tipo" value="3">

                            <div class="mb-3">
                                <label for="imagemCurso" class="form-label">Selecione a imagem (JPG, PNG ou WEBP):</label>
                                <input class="form-control" type="file" name="imagemCurso" id="imagemCurso" accept=".jpg,.jpeg,.png,.webp" required>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Enviar Imagem
                            </button>
                        </form>

                        <div id="respostaUpload" class="mt-3"></div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <div id="showfoto"></div>

            </div>
        </div>

        <!--  -->

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

    <script src="cursosv1.0/JS_cursoImgApresentacao.js?id=<?= $_GET['id']; ?>"></script>

    <script>

    </script>

    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainer">
    </div>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>