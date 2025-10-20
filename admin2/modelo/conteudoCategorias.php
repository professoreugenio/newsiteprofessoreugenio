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
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>

    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">

                <small><?= diadasemana($data, 2); ?></small>
                <h4>
                    CONTEÚDO PUBLICAÇÕES
                    <i class="bi bi-file-text-fill ms-2"></i>
                </h4>


            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/Subnav.php'; ?>

        </div>

        <?php

        // Consulta as categorias para a página específica
        $sql = "SELECT codigocursos, nome, comercialsc, onlinesc, visivelsc
        FROM new_sistema_cursos 
        WHERE codpagesadminsc = :codpagesadminsc AND visivelsc = 1 AND matriz = 1
        ORDER BY nome";
        $stmt = $con->prepare($sql);
        $stmt->execute([':codpagesadminsc' => 327]);

        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Função para contar publicações por categoria
        function contaPublicacoes($con, $codigocurso_sp)
        {
            $sql = "SELECT COUNT(*) FROM new_sistema_publicacoes_PJA WHERE codigocurso_sp = :codigocurso_sp";
            $st = $con->prepare($sql);
            $st->execute([':codigocurso_sp' => $codigocurso_sp]);
            return $st->fetchColumn();
        }
        ?>

        <?php require APP_ROOT . '/admin2/modelo/conteudos1.0/BodyConteudoCategorias.php'; ?>
        <hr>
        <?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>


    </div>

    <!-- Scripts -->
    <script src=" ../v1.0/PainelLateral.js"></script>



    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>