<nav class="navbar navbar-expand-lg navbar-dark">
    <!--  -->
    <div class="container-fluid">
        <!-- Lado esquerdo: Foto, nome, turma e link -->

        <div class="d-flex justify-content-between align-items-center w-100 px-1">
            <div class="d-flex align-items-center">
                <img onclick="window.location.href='modulo_status.php';" src="<?php echo $imgUser; ?>"
                    title="<?php echo $imgUser; ?>" alt="User Photo" class="user-photo" style="cursor: pointer;">

                <div id="descricao" class="ms-2">
                    <div onclick="window.location.href='modulo_status.php';" style="cursor: pointer;">
                        <div id="nmuser" class="text-white fw-bold mb-0">
                            <?php echo $nmUser; ?>
                        </div>
                        <div id="nmturma" class="text-white-50 mb-0" title="<?php echo $nomeTurma; ?>">
                            <?php echo $nomeTurma; ?>
                            <div id="nmsala">
                                <a href="modulo_status.php" class="text-white text-decoration-none badge bg-warning">Sala de
                                    Aula</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>


            <!-- Links da navbar -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <!-- <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Serviços</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contato</a>
                </li>
            </ul> -->

                <!-- Lado direito: Campo de pesquisa e botão Sair -->
                <div class="d-flex align-items-center ms-3">
                    <!-- <form class="d-flex me-3">
                    <input class="form-control me-2" type="search" placeholder="Pesquisar..." aria-label="Pesquisar">
                </form> -->
                   
                </div>
            </div>
        </div>



</nav>