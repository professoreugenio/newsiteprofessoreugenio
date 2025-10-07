<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <?php
            // LISTAGEM: somente itens ainda não acessados
            $stmt = config::connect()->prepare("
    SELECT 
        codigoForum, idusuarioCF, idartigoCF, idcodforumCF,
        textoCF, visivelCF, acessadoCF, dataCF, destaqueCF, horaCF
    FROM a_curso_forum
    WHERE acessadoCF = 0
    ORDER BY dataCF DESC, horaCF DESC
");
            $stmt->execute();
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class=" mt-4">
                    <i class="bi bi-chat-square-text me-2"></i>Avaliações pendentes
                </h5>
                <span class="badge bg-warning text-dark"><?= count($itens) ?> pendente(s)</span>
            </div>
            <div class="mt-4">
                <?php if (temPermissao($niveladm, [1])): ?>
                <?php require_once APP_ROOT . '/admin2/modelo/avaliacoes1.0/Subnav.php'; ?>
                <?php endif; ?>
            </div>

        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/avaliacoes1.0/BodyAvaliacoes.php'; ?>
    </div>
    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>