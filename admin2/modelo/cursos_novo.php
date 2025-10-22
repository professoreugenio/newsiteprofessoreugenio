<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php //require_once APP_ROOT . '/admin2/modelo/cursosv1.0/QueryCursos.php'; ?>
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
                <h3><i class="bi bi-journal-text me-2"></i> Novo Curso </h3>
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>

            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/Subnav.php'; ?>

        </div>

        <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/BodyCursoFormNovo.php'; ?>


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



    <script>
        $(document).ready(function() {
            $('#formNovoCurso').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'cursosv1.0/ajax_cursoInsertform.php',
                    data: formData,
                    dataType: 'json',
                    success: function(resposta) {
                        showToast(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');
                    },
                    error: function() {
                        showToast('Erro ao processar os dados. Tente novamente.', 'danger');
                    }
                });
            });

            function showToast(mensagem, tipo) {
                const toastId = 'toast-' + Date.now();
                const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white bg-${tipo} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                    <div class="d-flex">
                        <div class="toast-body">${mensagem}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                    </div>
                </div>
            `;
                $('#toastContainer').append(toastHtml);
                const toast = new bootstrap.Toast(document.getElementById(toastId));
                toast.show();
            }
        });
    </script>
    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainer">
    </div>
    <!-- Popper e Bootstrap -->
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>