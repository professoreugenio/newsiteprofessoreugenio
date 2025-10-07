<div class="position-relative">
    <button class="btn btn-primary mt-2" id="toggleListaModulos">Lista de módulos [+]</button>
    <div id="listaModulosLinks" class="mt-2 p-2 border rounded shadow bg-light position-absolute">
        <ul class="list-unstyled">
            <?php
            $query = $con->prepare("SELECT * FROM new_sistema_modulos_turmas_PJA,new_sistema_modulos_PJA WHERE new_sistema_modulos_turmas_PJA.codcurso = :id AND new_sistema_modulos_PJA.codigomodulos = new_sistema_modulos_turmas_PJA.codmodulo AND visivelm = '1' ORDER BY ordemm");
            $query->bindParam(":id", $idCurso);
            $query->execute();
            $fetchmdl = $query->fetchALL();
            $quant = count($fetchmdl);
            ?>
            <?php
            $i = "1";
            foreach ($fetchmdl as $key => $valDropDown) {
                $enc = encrypt($valDropDown['codigomodulos'], $action = 'e');
            ?>
            <li><a href="action.php?modulo=<?php echo $enc; ?>"
                    class="d-block p-2 rounded text-dark text-decoration-none"><i class="bi bi-laptop"></i>
                    <?php echo $valDropDown['modulo']; ?></a></li>
            <?php } ?>

        </ul>
    </div>
</div>