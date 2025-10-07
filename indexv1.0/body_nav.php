<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- <a class="navbar-brand" href="modelo-landpage4.html">Cursos Online</a> -->
        <?php require 'indexv1.0/body_nav_logo.php'; ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="./">Home</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="#cursos">Cursos</a>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cursosDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Cursos
                    </a>
                    <ul class="dropdown-menu animate-fade" aria-labelledby="cursosDropdown">
                        <!-- <li><a class="dropdown-item" href="#masterclass">Cursos Master Class</a></li> -->
                        <?php
                        $quant = "1";
                        $query = $con->prepare("SELECT * FROM new_sistema_categorias_PJA WHERE visivelhomesc = :var ");
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
                                $enc = encrypt("327&" . $value['codigocategorias'] . "&" . $idpublic, $action = 'e');
                            } else {
                                $enc = encrypt("327&" . $value['codigocategorias'] . "&" . $idpublic, $action = 'e');
                                $imgMidia = $raizSite . "/fotos/categorias/semfoto.png";
                            }

                        ?>
                            <li>
                                <a class="dropdown-item" href="action.php?curso=<?php echo $enc ?>">
                                    <?php echo $value['nome'];  ?> *
                                </a>
                            </li>

                        <?php } ?>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cursosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Páginas*
                    </a>
                    <ul class="dropdown-menu animate-fade" aria-labelledby="cursosDropdown">
                        <!-- <li><a class="dropdown-item" href="#masterclass">Cursos Master Class</a></li> -->
                        <?php
                        $pg = "1";
                        $numm = "0";
                        $var = "1";
                        $nome = "CURSOS";
                        $con = config::connect();

                        // Corrigindo a consulta
                        $query = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE home='1' ORDER BY ordemsp");
                        $query->execute();

                        // Obtendo os dados
                        $fetch = $query->fetchAll(); // Corrigido o nome da função para fetchAll() com A maiúsculo
                        $quant = count($fetch);

                        // Iterando sobre os resultados
                        foreach ($fetch as $key => $value) {
                            $idpgadmin = $value['codigopaginasadmin'];

                            // Corrigindo o valor que será criptografado (usando 'codigopaginasadmin')
                            $enc = encrypt($idpgadmin, $action = 'e');

                            $url = $enc;
                            $ord = $key + 1;
                            $active = "";

                            // Verificando se é a página ativa
                            if ($ord == $pg) {
                                $active = "active";
                            }
                        ?>
                            <li><a class="dropdown-item" href="action.php?idpage=<?php echo $url ?>">
                                    <?php echo $value['nomepaginapa']; ?>
                                </a></li>
                        <?php } ?>

                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#sobre">Sobre</a>
                </li>
                <!-- 

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cursosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Cursos
                    </a>
                    <ul class="dropdown-menu animate-fade" aria-labelledby="cursosDropdown">
                        <li><a class="dropdown-item" href="#masterclass">Cursos Master Class</a></li>
                        <li><a class="dropdown-item" href="#livres">Cursos Livres</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#sobre">Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contato">Contato</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view-teste.php">View</a>
                </li> -->
                <?php if (!empty($codigoUser)): ?>
                    <li class="nav-item">
                        <a style="background-color: red;color:white;cursor:pointer" class="nav-link" onclick="sair()">Sair*</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>