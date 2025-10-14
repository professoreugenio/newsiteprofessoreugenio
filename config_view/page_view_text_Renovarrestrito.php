<section class="restricted-section">
    <div class="restricted-container">
        <h1 class="restricted-title">Seja bem vindo</h1>
        <p class="restricted-message">
            Fico feliz por vê-lo aqui novamente!
        </p>
        <p>Tenho uma oferta incrível para você ter seu acesso ao conteúdo do seu curso e muito mais. Participar de Lives semanais, ter acesso às vídeo aulas, ter suporte com o priofessor e toda semana uma aula nova para você.</p>
        <p>Só clicar no link abaixo:</p>
        <?php
        //https://professoreugenio.com/action.php?curso=UGNBRHBKdHU1T1E2bEgyMld6a3A4Zz09
        ?>
        <?php
        if ($codigoUser == 115322) {
            $dec = encrypt($_GET['var'], $action = 'd');
            $exp = explode("&", $dec);
            echo $exp[0] . "&" . $exp[1]. "&" . $exp[2];
            $tipoc = "1";
            $decPagina = "0";
            $queryCat = $con->prepare("SELECT * FROM new_sistema_categorias_PJA WHERE codpagesadminsc = :var AND visivelhomesc ='1' AND comercialsc=:tipoc");
            $queryCat->bindParam(":var", $exp[0]);
            $queryCat->bindParam(":tipoc", $tipoc);
            $queryCat->execute();
            $rwCat = $queryCat->fetch(PDO::FETCH_ASSOC);
           echo $post = $rwCat['codigocategorias'];
            $idcurso;
            echo "<hr>";
            echo $dec = encrypt("UGNBRHBKdHU1T1E2bEgyMld6a3A4Zz09", $action = 'd');
        }
        ?>
        <hr>
        <?php   ?>
        <a href="https://professoreugenio.com/#cursos" class="subscribe-button">Renovar Assinatura</a>
    </div>
</section>