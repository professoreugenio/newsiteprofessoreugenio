<div class="modal-my-overlay" id="modal-my">
    <div class="modal-my">
        <div class="modal-my-header">
            <h3 style="color:#ffff00">📎 Anexos*</h3>
            <button class="close-btn">&times;</button>
        </div>
        <div class="modal-my-content">
            <p style="color:#ffffff">Baixe os arquivos complementares:</p>
            <ul>
                <?php $idcopia = $decPublic; ?>
                <?php
                $con = config::connect();
                $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_anexos_PJA WHERE codpublicacao = :var AND visivel = 1 ");
                $query->bindParam(":var", $idcopia);
                $query->execute();
                $fetch = $query->fetchALL();
                $quant = count($fetch);
                foreach ($fetch as $key => $value) {
                    $titulopa = $value['titulopa'];
                    $nr = $key + 1;
                    if ($value['urlpa'] != "#") {
                        $urlane = (' title="" href="') . $value['urlpa'] . ('" target="_blank" ');
                    } else {
                        $urlane = (' title="" href="anexos/publicacoes/') . $value['pastapa'] . ('/') . $value['anexopa'] . ('" target="_blank" download="anexos/publicacoes/') . $value['pastapa'] . ('/') . $value['anexopa'] . ('" ');
                    }
                ?>
                    <li><a <?= $urlane; ?>><i class="bi bi-file-earmark"></i> <?php echo $nr . " - " . $titulopa;  ?></a>
                    </li>
                <?php } ?>

            </ul>
        </div>
    </div>
</div>