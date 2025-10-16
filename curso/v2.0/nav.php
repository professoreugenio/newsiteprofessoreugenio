<?php $nomeTurma = $nomeTurma ?? 'Selecione o curso'; ?>
<nav data-aos="fade-right" class="navbar navbar-expand-lg navbar-custom fixed-top border-bottom border-secondary">
    <div class="container">
        <?php if (!empty($_COOKIE['startusuario'])): ?>
            <img onclick="window.location.href='modulo_status.php';" src="<?php echo $imgUser; ?>"
                title="Usuário" alt="User Photo" class="user-photo" style="cursor: pointer;">
            <div id="descricao" class="ms-2">
                <div onclick="window.location.href='modulo_status.php';" style="cursor: pointer;">
                    <div id="nmuser" class="text-white fw-bold mb-0">
                        <?php echo $nmUser; ?>
                    </div>
                    <div id="nmturma" class="text-white-50 mb-0" title="<?php echo $nomeTurma; ?>">
                        <?php echo $nomeTurma; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <img onclick="window.location.href='../';" src="https://professoreugenio.com/img/logo.png" style="height: 40px;" alt="">
        <?php endif; ?>
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <i class="bi bi-upc-scan"></i>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Navbar links com ícones -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" style="cursor: pointer;" onclick="window.location.href='../';">
                            <i class="bi bi-house-door-fill me-1"></i> Home
                        </a>
                    </li>
                    <!-- Dropdown com ícone -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="cursosDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-mortarboard me-1"></i> Cursos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="cursosDropdown">

                            <?php
                            $quant = "1";
                            $query = $con->prepare("SELECT * FROM new_sistema_cursos WHERE visivelhomesc = :var ");
                            $query->bindParam(":var", $quant);
                            $query->execute();
                            $fetch = $query->fetchALL();
                            $quant = count($fetch);
                            foreach ($fetch as $key => $value) {
                                $pasta = $value['pasta'];
                                $tipo = "1";

                                $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA WHERE pasta = :pasta AND tipo = :tipo ");
                                $query->bindParam(":pasta", $pasta);
                                $query->bindParam(":tipo", $tipo);
                                $query->execute();
                                $rwImagem = $query->fetch(PDO::FETCH_ASSOC);
                                $enc = null;
                                $idpublic = null;
                                if ($rwImagem) {
                                    $imgMidia = $raizSite . "/fotos/categorias/" . $rwImagem['pasta'] . "/" . $rwImagem['foto'];
                                    $idpublic = $rwImagem['codigomidiasfotos'];
                                    $enc = encrypt("327&" . $value['codigocursos '] . "&" . $idpublic, $action = 'e');
                                } else {
                                    $enc = encrypt("327&" . $value['codigocursos '] . "&" . $idpublic, $action = 'e');
                                    $imgMidia = $raizSite . "/fotos/categorias/semfoto.png";
                                }

                            ?>
                                <li>
                                    <a class="dropdown-item" href="../action.php?curso=<?php echo $enc ?>">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> <?php echo $value['nome'];  ?>
                                    </a>
                                </li>

                            <?php } ?>

                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://professoreugenio.com/action.php?idpage=ZFZDdkVwN2RDa0plVWdienNUQTRqdz09">
                            <i class="bi bi-envelope-fill me-1"></i> Contato
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" onclick="sair()">
                            <i class="bi bi-power text-danger"></i> Sair
                        </a>
                    </li>

                </ul>
            </ul>
        </div>
    </div>
</nav>