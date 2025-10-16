<section class="restricted-section">
    <div class="restricted-container text-center">
        <h2 class="restricted-title"><?php echo $tituloPublicacao;  ?></h2>
        <h3>Conteúdo Exclusivo</h3>
        <p class="restricted-message pt-4 text-center" style="max-width: 600px;margin:10px auto">
            Esse material é só para quem é da casa! Junte-se aos mais de 1500 alunos que já estão aproveitando tudo: vídeos, conteúdos especiais e dicas práticas. Bora fazer parte dessa turma?
        </p>
        <?php $decVar;  ?>
        <?php
        $quant = "100";
        $query = $con->prepare("SELECT * FROM new_sistema_cursos WHERE  visivelhomesc = :var ");
        // $query->bindParam(":idcategoria", $idCurso);
        $query->bindParam(":var", $quant);
        $query->execute();
        $fetch = $query->fetchALL();
        $quant = count($fetch);
        foreach ($fetch as $key => $value) {

            $idcategoria = $value['codigocursos '];
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
                <a class="dropdown-item" href="action.php?curso=<?php echo $enc ?>">
                    <?php echo $value['nome'];  ?>
                </a>
            </li>

        <?php } ?>
        <a id="btloginaluno" href="login_aluno.php?ts=1747648423" class="btn btn-aluno">
            <i class="bi bi-person-circle me-2"></i> Aluno
        </a>
        <div class="pt-4">
            <a href="#" class="subscribe-button mt-4">Adquirir Assinatura</a>
        </div>

        <div id="contato" class="container text-center pt-4">
            <h2 class="pt-4">Fale Conosco</h2>

            <div class="mt-4">
                <a href="https://professoreugenio.com/action.php?idpage=ZFZDdkVwN2RDa0plVWdienNUQTRqdz09" class="btn btn-outline-light me-2">
                    <i class="bi bi-envelope"></i> Enviar E-mail
                </a>

            </div>
            <div class="mt-4">

                <a href="https://wa.me/5585996537577" target="_blank" class="btn btn-whatsapp">
                    <i class="bi bi-whatsapp"></i>
                    Falar no WhatsApp</a>
            </div>
        </div>

    </div>
</section>