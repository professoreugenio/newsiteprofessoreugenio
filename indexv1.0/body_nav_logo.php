<!-- <a class="navbar-brand" href="#">Cursos Online</a> -->
<div class="logo">
    <?php
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
        $exp = explode("&", $decUser);
        $codigoUser = $exp[0];
        $nmc = "sc";
        $tag = ('<span style="color:black">Prof: </span>');
        $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario=:id ");
        $query->bindParam(":id", $codigoUser);
        $query->execute();
        $rwUser = $query->fetch(PDO::FETCH_ASSOC);
        $foto = $rwUser['imagem200'];
        $pasta = $rwUser['pastasu'];
        $nomeUser = $rwUser['nome'];
        $expimg = explode(".", $foto);
        $img = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
        if ($foto == "usuario.jpg") {
            $img = $raizSite . "/fotos/usuarios/" . $foto;
        }
        $chaveturmaUser = "";
        $nomeTurma = "Não definido";
        $idTurma = "";
        $chaveTurma = "";
        if (!empty($exp[4])) {
            $idTurma = $exp[4];
            $chaveturmaUser = $exp[5];
            $queryTurma = $con->prepare("SELECT * FROM  new_sistema_cursos_turmas WHERE codigoturma = :idsubcat ");
            $queryTurma->bindParam(":idsubcat", $idTurma);
            $queryTurma->execute();
            $rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);
            if (!empty($rwTurma)) {
                $nomeTurma = $rwTurma['nometurma'];
                $chaveTurma = $rwTurma['chave'];
                $idcurso = $rwTurma['codcursost'];
            } else {
                $nomeTurma = "";
                $chaveTurma = "";
                $idcurso = "";
            }
        }
        require 'indexv1.0/headusuariostart.php';
    } else {

        if (!empty($_COOKIE['startusuario'])) {
            $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
            $exp = explode("&", $decUser);
            $codigoUser = $exp[0];
            if (empty($exp[4])) {


                echo ('<meta http-equiv="refresh" content="0; url=curso/turmas.php">');
                exit();
            }
            $idTurma = $exp[4];
            $chaveturmaUser = "";
            $nomeTurma = "Não definido";
            $tag = "";
            if (!empty($exp[4])) {
                $idTurma = $exp[4];
                $chaveturmaUser = $exp[5] ?? "";
                $queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idsubcat");
                $queryTurma->bindParam(":idsubcat", $idTurma);
                $queryTurma->execute();
                $rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);

                if (!empty($rwTurma)) {
                    $nomeTurma = $rwTurma['nometurma'] ?? "Sem título";
                    $chaveTurma = $rwTurma['chave'] ?? "";
                    $idcurso = $rwTurma['codcursost'] ?? "";
                }
            }


            $con = config::connect();
            $query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:id ");
            $query->bindParam(":id", $codigoUser);
            $query->execute();
            $rwUser = $query->fetch(PDO::FETCH_ASSOC);
            $foto = $rwUser['imagem200'];
            $pasta = $rwUser['pastasc'];
            $nomeUser = $rwUser['nome'];
            $expimg = explode(".", $foto);
            $img = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
            if ($foto == "usuario.jpg") {

                $img = $raizSite . "/fotos/usuarios/" . $foto;
            }
    ?>
            <div class="flex-start" id="userhead">

                <a href="curso/turmas.php">
                    <div class="fotouserNav" style="background-image: url(<?php echo $img; ?>);"></div>
                </a>
                <a href="curso/turmas.php">
                    <div class="userdados">
                        <div class="NomeUser">
                            <?php echo $tag; ?> <?php echo nome($nome = $nomeUser, $n = "2"); ?>
                        </div>
                        <div class="nomeTurma"><?php echo $nomeTurma; ?></div>
                        <div class="nomesala">Sala de Aula</div>
                    </div>
                </a>
                <div id="alertapopupmsg"></div>
            </div>

    <?php
        } else {
            echo ('<a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
                          <h2 class="m-0 text-primary"><img src="img/logo.png" width="100px" alt=""></h2>
                          </a>');
        }
    }
    // todo
    ?>
    <!-- Uncomment below if you prefer to use an image logo -->
    <!-- <a href="index.html"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->
</div>