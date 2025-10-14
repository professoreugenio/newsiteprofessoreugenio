<?php if ($aut == 1):
    require 'config_view/modal_topicosOn.php';
else:
    require 'config_view/modal_topicosOff.php';

endif;

?>

<div class="floating-container-topicos">
    <?php if ($aut == 1): ?>
        <button class="fab-btn-topicos" data-bs-toggle="modal" data-bs-target="#modalLinks">
            <i class="bi bi-list"></i> Tópicos *
            
            
        </button>

    <?php else: ?>
        <button class="fab-btn-topicos" id="openModal">
            <i class="bi bi-list"></i> Tópicos
        </button>
    <?php endif; ?>
    <!-- <div class="fab-menu-topicos" id="fabMenu">
        <a href="#topico1">Tópico 1</a>
        <a href="#topico2">Tópico 2</a>
        <a href="#topico3">Tópico 3</a>
        <a href="#topico4">Tópico 4</a>
    </div> -->
</div>