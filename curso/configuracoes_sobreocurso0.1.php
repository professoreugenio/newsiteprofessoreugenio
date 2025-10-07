<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'config_default1.0/query_dados.php' ?>
    <?php require 'config_curso1.0/query_curso.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_curso1.0/v2.0/query_publicacoes.php' ?>
    <?php require 'config_curso1.0/query_anexos.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Curso de <?= $nomeTurma; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="v2.0/config_css/config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>
    <link rel="stylesheet" href="v2.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <style>
        .box-licoes {
            display: none;
        }
    </style>

    <link rel="stylesheet" href="perfilv2.0/perfil.css">
</head>

<body style="min-height: 800px;">
    <?php if ($tipocurso == 0) :
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas">');
        exit();
    endif; ?>

    <?php $nav = "3";
    require 'perfilv2.0/nav_configuracoes.php' ?>
    <!-- Navbar -->
    <?php include 'v2.0/nav.php'; ?>
    <?php $quantAnexo = "0"; ?>
    <?php require 'v2.0/sidebarLateral.php'; ?>
    <?php require 'v2.0/sidebarLateralLicoes.php'; ?>
    <!-- Conteúdo -->
    <main class="container">
        <div class="row justify-content-center">
            <!-- Conteúdo Principal da Aula -->
            <div class="col-md-12 col-lg-12">
                <div class="info-curso bg-white p-4 rounded shadow-sm">
                    <?php require 'perfilv2.0/sobreocurso.php' ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <?php require 'v2.0/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script>
        function abrirPagina(url) {
            window.open(url, '_self');
        }
    </script>

    <script>
        // Clique no botão de atualização
        document.getElementById('btnupdate').addEventListener('click', function() {
            const form = document.getElementById('idformupdate');
            const btn = this;

            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Atualizando...';

            fetch('perfilv2.0/updateperfil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    showToast(res.mensagem, res.sucesso ? 'success' : 'danger');
                    if (res.sucesso) {
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-success');
                    }
                })
                .catch(() => {
                    showToast('Erro ao atualizar. Tente novamente.', 'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Atualizar <i class="bi bi-save"></i>';
                });
        });

        // Toast Bootstrap personalizado
        function showToast(mensagem, tipo = 'info') {
            const toastContainer = document.getElementById('toast-container') || criarContainerToast();
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${tipo} border-0 show mb-2`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${mensagem}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
            toastContainer.appendChild(toastEl);
            setTimeout(() => toastEl.remove(), 4000);
        }

        function criarContainerToast() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }
    </script>

    <!-- <script src="perfilv2.0/JS_updateperfil.js?<?= time(); ?>"></script> -->
    <script src="scripts/registraacessos.js?<?= time(); ?>"></script>
    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
    <script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
</body>

</html>