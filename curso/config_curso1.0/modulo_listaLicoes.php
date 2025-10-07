<span class="badge bg-success" onclick="window.location.href='../curso/modulos.php';">Acessar módulos</span>
<div onclick="window.location.href='modulo_status.php';" id="nmModuloAula" class="">
    <i class="bi bi-chevron-left me-2 fs-4"></i> Voltar <?= htmlspecialchars($nmmodulo) ?>
</div>
<div class="mb-2"><?php echo $quantLicoes;  ?> Lições neste Módulo</div>
<div class="lista-licoes" style="position: sticky;top: 80px; height: 70vh; overflow-y: auto;">
    <ul class="list-group list-group-flush">
        <?php
        foreach ($fetchTodasLicoes as $key => $value) { ?>
            <?php $codigoaulas = $value['codigopublicacoes']; ?>
            <?php
            $check = '<div class="seta-direita-off"></div>';
            $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario  ");
            $query->bindParam(":codigoaula", $codigoaulas);
            $query->bindParam(":codigousuario", $codigousuario);
            $query->execute();
            $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);
            if ($rwaulavista):
                $check = '<div class="seta-direita"></div>';
            endif
            ?>
            <?php $num = $key + 1; ?>
            <?php $enc = encrypt($value['idpublicacaopc'], $action = 'e'); ?>
            <?php
            $selecionada = ($codigoaulas == $codigoaula && $startModo == '1') ? 'li-selecionada' : '';
            ?>
            <li class="list-group-item bg-dark text-light p-2 <?php echo $selecionada; ?>" id="<?php echo $enc; ?>">
                <a class="text-decoration-none text-light d-block"
                    onclick="window.location.href='actionCurso.php?lc=<?php echo $enc; ?>';">
                    <?php echo $check;  ?>
                    <?php echo $num;  ?>
                    <?php echo $value['titulo']; ?>
                </a>
            </li>
        <?php }
        ?>
    </ul>
</div>
<i class="fa fa-hand-o-down" aria-hidden="true"></i>