<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2)); // sobe 2 níveis
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
</head>

<body id="adminLayout">
    <!-- NAVBAR -->
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>

    <!-- Painel Lateral Deslizante -->
    <div id="painelLateral" class="position-fixed top-0 start-0 bg-white shadow rounded-end p-4"
        style="width: 260px; height: 100vh; z-index: 1050; transform: translateX(-100%); transition: 0.3s ease;">
        <!-- Botão de fechar -->
        <button class="btn-close position-absolute top-0 end-0 m-3" onclick="fecharPainel()" aria-label="Fechar"></button>

        <h5 class="mb-4 mt-4 text-primary"><i class="bi bi-grid-fill me-2"></i>Menu Rápido</h5>

        <!-- Publicações -->
        <div>
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('publicacoes')">
                <i class="bi bi-journal-text me-2"></i> Publicações [+]
            </a>
            <div id="menu-publicacoes" class="ps-3 d-none">
                <a href="#" class="d-block text-muted mb-1">- Cursos</a>
                <a href="#" class="d-block text-muted mb-1">- Acessos</a>
                <a href="#" class="d-block text-muted mb-1">- Anúncios</a>
                <a href="#" class="d-block text-muted mb-1">- Mensagens</a>
                <a href="#" class="d-block text-muted mb-1">- Alunos</a>
                <a href="#" class="d-block text-muted mb-1">- Promoções</a>
            </div>
        </div>

        <!-- Páginas -->
        <div>
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('paginas')">
                <i class="bi bi-files me-2"></i> Páginas [+]
            </a>
            <div id="menu-paginas" class="ps-3 d-none">
                <a href="#" class="d-block text-muted mb-1">- Home</a>
                <a href="#" class="d-block text-muted mb-1">- Contato</a>
                <a href="#" class="d-block text-muted mb-1">- Termos</a>
            </div>
        </div>

        <!-- Configurações -->
        <div>
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('config')">
                <i class="bi bi-gear-fill me-2"></i> Configurações [+]
            </a>
            <div id="menu-config" class="ps-3 d-none">
                <a href="#" class="d-block text-muted mb-1">- Páginas Admin</a>
                <a href="#" class="d-block text-muted mb-1">- Administradores</a>
                <a href="#" class="d-block text-muted mb-1">- Dados /</a>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="container-fluid px-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <div class="mt-4">
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>
                <h4 class="mt-2">
                    <small class="text-muted">Bem-vindo de volta, <strong><?= $nomeadm ?? 'Administrador'; ?></strong>!</small>
                </h4>
            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear-fill me-1"></i> Configurações
                </button>
            </div>
        </div>

        <!-- Cards -->
        <div class="row g-4">
            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="bi bi-people-fill me-2"></i> Usuários</h5>
                        <p class="card-text fs-4 fw-bold">132</p>
                        <small class="text-muted">Usuários ativos no sistema</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="bi bi-journal-text me-2"></i> Cursos</h5>
                        <p class="card-text fs-4 fw-bold">18</p>
                        <small class="text-muted">Cursos publicados</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><i class="bi bi-folder-fill me-2"></i> Conteúdos</h5>
                        <p class="card-text fs-4 fw-bold">245</p>
                        <small class="text-muted">Materiais disponíveis</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3" data-aos="fade-up">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><i class="bi bi-chat-left-text-fill me-2"></i> Mensagens</h5>
                        <p class="card-text fs-4 fw-bold">7</p>
                        <small class="text-muted">Novas mensagens</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Abrir/Fechar painel
        document.querySelector('.navbar-brand').addEventListener('click', function(e) {
            e.preventDefault();
            const painel = document.getElementById('painelLateral');
            painel.style.transform = painel.style.transform === 'translateX(0%)' ?
                'translateX(-100%)' :
                'translateX(0%)';
        });

        function abrirMenu(menu) {
            const menus = ['publicacoes', 'paginas', 'config'];
            menus.forEach(m => {
                document.getElementById('menu-' + m).classList.add('d-none');
            });
            document.getElementById('menu-' + menu).classList.remove('d-none');
        }

        function fecharPainel() {
            document.getElementById('painelLateral').style.transform = 'translateX(-100%)';
        }
    </script>

    <!-- AOS Animation -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>

    <!-- Bootstrap + jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>