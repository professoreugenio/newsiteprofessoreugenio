<div class="flex-start" id="userhead">
    <a href="curso/">
        <div class="fotouserNav" style="background-image: url(<?php echo $img; ?>);"></div>
    </a>
    <a href="curso/">
        <div class="userdados">
            <div class="NomeUser">
                <?php echo nome($nome = $nomeUser, $n = "2"); ?>
            </div>
            <div class="nomeTurma"><?php echo $nomeTurma; ?></div>
            <div class="nomesala">
            </div>
        </div>
    </a>
    <div id="alertapopupmsg"></div>
</div>