<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require '../modulos/head.php' ?>
    <link rel="stylesheet" href="../mycss/config.css">
    <link rel="stylesheet" href="../mycss/menubottom.css">
    <link rel="stylesheet" href="../mycss/submenu.css">
</head>

<body>
    <!-- Navbar -->
    <?php require '../modulos/nav.php' ?>
    <!-- Offcanvas Sidebar -->
    <?php require '../modulos/offcanvas.php' ?>
    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content Area -->
            <main class="col-md-6 ms-sm-auto col-lg-6 px-md-4">
                <?php
                require '../classes/classe_submenupagina.php';
                $menu->addLinksub('./', 'bi bi-box-arrow-in-left', 'Home', 'active');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->addLinksub('novo.php', 'bi bi-star-fill text-warning', ' Novo ', '');
                $menu->render(); ?>

                <div class="content">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <!-- Your main content goes here -->
                            <p>Bem-vindo ao painel de administração!</p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat veritatis rem et ullam sint vero quidem
                            id quod similique soluta voluptatum in optio, explicabo quis saepe non earum doloremque tempora, ad
                            nostrum maiores, quibusdam voluptas atque! Adipisci, tempora? Recusandae alias mollitia animi. Quo
                            praesentium culpa magnam incidunt odit. Dolorum quis quam omnis at tempora earum, quae veniam officiis
                            cupiditate non neque quibusdam inventore et. Nobis iste fugit illum nam est dolor sed mollitia! Accusamus
                            nostrum in, ratione modi earum quo aliquam quisquam animi a nemo facilis tempore inventore perspiciatis,
                            dolore praesentium impedit consequatur quaerat assumenda fugiat debitis mollitia, quibusdam excepturi
                            unde? Rem eius molestias provident architecto sapiente quae facilis hic earum? Beatae nemo quaerat, natus
                            recusandae non, libero architecto est nihil labore doloribus reiciendis cumque atque ducimus placeat
                            dolore facilis accusamus assumenda esse velit odit, totam corrupti quas vero necessitatibus. Tempora sint
                            accusamus vel dolore exercitationem consequuntur odio aut, rem tenetur aperiam culpa fugiat eligendi?
                            Sapiente aut vitae nostrum magni iure dolorem, fuga explicabo amet! Cupiditate inventore asperiores cum,
                            nemo quod distinctio magni, doloribus vitae modi temporibus ducimus laborum repudiandae earum corporis
                            nobis odio iste labore culpa atque veritatis sapiente qui impedit assumenda! Adipisci, eveniet delectus
                            nulla facilis sequi id cupiditate inventore illum dolores necessitatibus dignissimos sed assumenda
                            mollitia deleniti perspiciatis facere illo? Sapiente quibusdam minima repellat veritatis temporibus velit
                            non repellendus laudantium esse, dolorem atque iure ducimus repudiandae nisi modi? Rem iste repellendus
                            iusto. Inventore impedit harum ipsum vitae odio. Quis voluptatum officia tenetur molestiae debitis facere
                            sunt, quisquam, laudantium, nam nobis eligendi facilis alias placeat provident vero natus! Quis incidunt
                            officiis dicta totam dolorum, praesentium accusantium similique adipisci inventore rem architecto,
                            laboriosam quas sit reprehenderit consequuntur cumque in! Eum tenetur molestiae facilis id facere iure
                            exercitationem accusantium, maiores non quos officiis minima modi! Eum alias aperiam fugiat velit!
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php
    require '../modulos/menubottom.php';
    $menu->addLink('../sair', 'fas fa-sign-out-alt', 'Sair');
    $menu->render(); ?>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js"></script>
    <script>
        feather.replace();
    </script>
    <script>
        // Ativar tooltips do Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>

</html>