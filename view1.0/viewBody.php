<body>
    <div id="ipRegistro" data-id="123.45.67.89"></div>
    <!-- Navbar -->
    <?php include 'defaultv1.0/body_navall.php'; ?>
    <?php $nmUserIp;  ?>
    <?php
    if (isset($_COOKIE['startusuario'])) {
        $cookieContent = $dec = encrypt($_COOKIE['startusuario'], $action = 'd');
        $lines = explode("&", $cookieContent);
        foreach ($lines as $key => $line) {
            // echo $key . " { " . $line . "}<br>";
        }
    } else {
        // echo "O cookie 'startusuario' não está definido.";
    }
    ?>

    <?php include 'view1.0/sectionConteudo.php'; ?>
    <!-- Canvas 1 -->
    <?php require  'config_view/page_view_canvas.php'; ?>
    <!-- Footer -->
    <?php require 'config_default/link_adm.php'; ?>

    <?php require 'config_default/link_adm.php'; ?>
    <div class="container-mascote">
        <img id="personagem" src="https://professoreugenio.com/img/mascotes/professor.png" alt="Personagem">
        <div id="balao">
            <span class="fechar" onclick="fecharBalao()">X</span>
        </div>
    </div>
    <!-- <script src="config_default/JS_frasesmascote2.js?<?php echo time(); ?>"></script> -->
    <?php // if ($autorizado): echo '<button id="botaoDicas">+ Dicas</button>';
    // endif; 
    ?>
    <?php ?>

    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">&copy; 2025 Cursos Online. Todos os direitos reservados.</p>
    </footer>
    <?php require 'config_default_js/scrollToTop.php' ?>
    <script src=" config_default/JS_logoff.js"></script>
    <?php require 'config_view/modal_anexo.php'; ?>
    <script src="config_view_js/botao_liberaAtividade.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <?php if ($aut == 1): ?>
        <!-- <script src="config_view/JS_topicos_onh2h5.js?<?php echo time(); ?>"></script> -->
    <?php else: ?>
        <!-- <script src=" config_view/JS_topicos_offh2h5.js?<?php echo time(); ?>"></script> -->
    <?php endif; ?>
    <?php /*if ($autorizado): require 'config_view/floatingtopicos.php';
    endif;*/ ?>
    <?php if ($codigoUser == 1) : ?>


        <!-- <script>
            function atualizarUsuariosOnline() {
                fetch('config_chat/usuarios_online.php')
                    .then(response => response.json())
                    .then(data => {
                        const lista = document.getElementById('listaUsuarios');
                        const total = document.getElementById('totalOnline');
                        const hora = document.getElementById('horaAtual');

                        lista.innerHTML = '';
                        total.textContent = data.total;

                        if (data.usuarios.length === 0) {
                            lista.innerHTML = '<li class="list-group-item text-center text-muted">Nenhum usuário online</li>';
                        } else {
                            data.usuarios.forEach(user => {
                                const item = document.createElement('li');
                                item.className = 'list-group-item d-flex justify-content-between align-items-center';
                                item.innerHTML = `
            <span>${user.chaveso}</span>
            <span class="badge bg-secondary">${new Date(user.ultimoping * 1000).toLocaleTimeString()}</span>
          `;
                                lista.appendChild(item);
                            });
                        }

                        const agora = new Date();
                        hora.textContent = agora.toLocaleTimeString();
                    })
                    .catch(err => {
                        document.getElementById('listaUsuarios').innerHTML = `
        <li class="list-group-item text-danger">Erro ao carregar: ${err}</li>
      `;
                    });
            }

            setInterval(atualizarUsuariosOnline, 8000);
            atualizarUsuariosOnline(); // chama na primeira vez
        </script> -->
    <?php endif; ?>


    <script src="config_chat/JS_chatmsgs.js?<?= time() ?>"></script>



    <script src="scripts/registraacessos.js?<? time(); ?>"></script>
    <script src="config_chat/quemonline.js?<? time(); ?>"></script>
    <!-- <script src="config_view_js/floatingtopicos.js"></script> -->
    <input type="hidden" name="nraula" id="nraula" value="AULA <?php echo $nrAula;  ?>">
    <input type="hidden" name="bgcolor" id="bgcolor" value="<?php echo $bgcolor;  ?>">
    <input type="hidden" name="urlpost" id="urlpost" value="<?php echo $paginaatual;  ?>">
    <input type="hidden" name="modulo" id="modulo" value="<?php echo $titulomodulo;  ?>">
    <!-- SIDEBARNOVO -->
    <?php require 'config_view/sidebarNovo.php' ?>
    <button id="toggleFloatingBtn-menu" title="Mostrar Menu">
        <i class="bi bi-list"></i>
    </button>
    <script src="config_view/JS_offCanvasLicoes.js?<?= time(); ?>"></script>
    <!-- FIM SIDEBAR -->
    <!-- CHAT -->

    <!-- FIM CHAT -->
    <script src="config_view_js/addlicoes.js?<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="config_view_js/modal_anexo.js"></script>
    <script src="config_view/JS_sidebarNovo.js?= time(); ?>"></script>
</body>