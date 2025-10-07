<div id="painelLateral" class="position-fixed top-0 start-0 bg-white shadow rounded-end p-4"
    style="width: 260px; height: 100vh; z-index: 1050; transform: translateX(-100%); transition: transform 0.3s ease;">
    <button class="btn-close position-absolute top-0 end-0 m-3" onclick="fecharPainel()" aria-label="Fechar"></button>
    <h5 class="mb-4 mt-4 text-primary"><i class="bi bi-grid-fill me-2"></i>Menu Rápido *</h5>
    <?php
    function ConsultaArray($con, $inicio, $limite)
    {
        $queryArray = $con->prepare("SELECT * FROM sistema_sessao WHERE visivel = '1'   ORDER BY ordem ASC LIMIT $inicio,$limite");
        $queryArray->execute();
        return $queryArray->fetchALL();
    }
    ?>
    <?php
    $resultarray = ConsultaArray($con, '0', '100');
    $quant = count($resultarray);
    ?>

    <?php foreach ($resultarray as $key => $rw_Sessao) { ?>
        <?php $idSes = $rw_Sessao['codigosessao']; ?>
        <?php $encSessao = encrypt($rw_Sessao['codigosessao'], $action = 'e'); ?>
        <div>
            <a href="#" class="d-block mb-2 text-dark fw-bold" onclick="abrirMenu('<?php echo $rw_Sessao['tag']; ?>')">
                <i class="bi bi-journal-text me-2"></i>
                <?php echo $rw_Sessao['nome']; ?>
                <span id="toggle-<?php echo $rw_Sessao['tag']; ?>" class="toggle-seta">▶</span>
            </a>
            <div id="menu-<?php echo $rw_Sessao['tag']; ?>" class="submenu ps-3">
                <?php
                $queryCategoria = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE codsessao = '$idSes' AND visivelpa = '1' ORDER BY ordemsp ASC LIMIT 0,20");
                $queryCategoria->execute();
                $resultarrayCategoria = $queryCategoria->fetchALL();
                foreach ($resultarrayCategoria as $rwPagina) {
                ?>
                <?php $encPage = encrypt($rwPagina['codigopaginasadmin'], $action = 'e'); ?>
                    <a href="../actions.php?ses=<?php echo $encSessao; ?>&page=<?php echo $encPage; ?>" class="d-block text-muted mb-1">:: <?php echo $rwPagina['nomepaginapa']; ?></a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>