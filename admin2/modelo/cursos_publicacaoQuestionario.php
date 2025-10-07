<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/QueryCursos.php'; ?>
<?php require_once APP_ROOT . '/admin2/modelo/modulosv1.0/QueryModulos.php'; ?>
<?php require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/QueryPublicacao.php'; ?>
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


    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

</head>

<body id="adminLayout">

    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
        <div id="toastGeneric" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div id="toastBody" class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <script>
        // Helpers de Toast
        let toastEl = null,
            toastInstance = null;

        function ensureToast() {
            if (!toastEl) {
                toastEl = document.getElementById('toastGeneric');
                toastInstance = bootstrap.Toast.getOrCreateInstance(toastEl, {
                    delay: 2200
                });
            }
        }

        function showToast(msg, variant = 'primary', autohide = true) {
            ensureToast();
            const body = document.getElementById('toastBody');
            body.innerHTML = msg;
            toastEl.className = 'toast align-items-center border-0 text-white bg-' + variant;
            toastInstance._config.autohide = autohide;
            toastInstance.show();
        }

        // Loading Toast (com spinner)
        let loadingCount = 0;

        function showLoading(msg = 'Processando...') {
            loadingCount++;
            showToast(`<span class="spinner-border spinner-border-sm me-2"></span>${msg}`, 'dark', false);
        }

        function hideLoading() {
            if (loadingCount > 0) loadingCount--;
            if (loadingCount === 0) {
                ensureToast();
                toastInstance.hide();
            }
        }
    </script>


    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>


    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> Módulo <?= $Nomecurso ?></h3>
            </div>
            <div class="mt-4">
                <?php require_once APP_ROOT . '/admin2/modelo/publicacoesv1.0/SubnavSecundarioPublicacoes.php'; ?>
            </div>
            <div class="mt-4">
                <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/SubnavCursosEditar.php'; ?>
            </div>
        </div>
        <!-- CORPO PÁGINA -->
        <?php require_once APP_ROOT . '/admin2/modelo/questionariov1.0/BodyPublicacaoListaQuestionario.php'; ?>
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
    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainer">
    </div>
    <!-- Popper e Bootstrap -->
    <script src="publicacoesv1.0/JS_publicacoesEditarTextoExcluir.js"></script>

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>