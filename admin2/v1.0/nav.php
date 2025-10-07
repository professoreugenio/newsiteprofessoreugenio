<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow fixed-top">
    <div class="container-fluid">
        <!-- Logo ou Nome -->

        <?php if (temPermissao($niveladm, [1])): ?>
            <a onclick="abrirPainel()" class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-speedometer2 me-2"></i> Painel Admin
            </a>

        <?php endif; ?>
        <!-- Botão responsivo -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Itens do menu -->
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php"><i class="bi bi-house-door-fill me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="alunos.php?todos=1"><i class="bi bi-people-fill me-1"></i>Usuários</a>
                </li>
                <!-- 
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-book-fill me-1"></i>Cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-folder-fill me-1"></i>Conteúdos</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" target="_blank" href="../"><i class="bi bi-folder-fill me-1"></i>site</a>
                </li>
            </ul>
            <!-- Perfil do usuário -->
            <div class="dropdown">
                <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" href="#">
                    <img src="<?php echo $img;  ?>" alt="avatar" width="32" height="32" class="rounded-circle me-2">
                    <strong><?php echo $nomeadm;  ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="perfiladmin.php?id=<?= $idEnc; ?>"><i class="bi bi-person-circle me-2"></i>Perfil</a></li>
                    <?php if (temPermissao($niveladm, [1,])): ?>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear-fill me-2"></i>Configurações</a></li>

                    <?php endif; ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" id="btnLogoff">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a>
                    </li>




                    <!-- <script>
                        $(document).ready(function() {
                            $("#btnLogoff").on("click", function(e) {
                                e.preventDefault();

                                if (confirm("Tem certeza que deseja sair do sistema?")) {
                                    $.ajax({
                                        url: "../../defaultv1.0/logoff.php",
                                        method: "POST",
                                        success: function(d) {
                                            window.open("../", "_self");
                                            window.close();
                                        },
                                        error: function() {
                                            alert("Erro ao tentar sair. Tente novamente.");
                                        }
                                    });
                                }
                            });
                        });
                    </script> -->


                </ul>
            </div>
        </div>
    </div>
</nav>