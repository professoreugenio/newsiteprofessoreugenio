<!-- Menu lateral -->
<div id="sidebar_config">

    <button class="sidebar_config-btn <?php if ($nav == 0): echo " active";
                                        endif; ?>" onclick="window.location.href='modulo_status.php'" title="Voltar para lições">
        <i class="bi bi-house-fill"></i>
    </button>
    <button class="sidebar_config-btn <?php if ($nav == 1): echo " active";
                                        endif; ?>" onclick="window.location.href='configuracoes_perfil.php'" title="Perfil">
        <i class="bi bi-person-fill"></i>
    </button>

    <button class="sidebar_config-btn <?php if ($nav == 2): echo " active";
                                        endif; ?>" onclick="window.location.href='configuracoes_foto.php'" title="Foto">
        <i class="bi bi-camera-fill"></i>
    </button>

    <button class="sidebar_config-btn <?php if ($nav == 3): echo " active";
                                        endif; ?>" onclick="window.location.href='configuracoes_sobreocurso.php';" title="Foto">
        <i class="bi bi-mortarboard-fill"></i>
    </button>

   

    <!-- <button class="sidebar_config-btn <?php if ($nav == 3): echo " active";
                                            endif; ?>" onclick="window.location.href='configuracoes_turma.php'" title="Turma">
        <i class="bi bi-gear-fill"></i>
    </button> -->
</div>