<?php
$encatv = encrypt($idmsg . "&" . $idoriginal, $action = 'e');
?>
<!-- Floating Action Button -->
<div class="fab-container" id="fabContainer">
    <button class="fab-btn" onclick="toggleFab()" id="fabToggleBtn" data-bs-toggle="tooltip"
        title="Clique e Acesse + Lições , Anexos e módulos do curso">+*</button>
    <div class="fab-options" id="fabOptions">
        <?php
        if (empty($decModulo)) {
            $decModulo = "0";
        };
        
        ?>
        <?php
        if (!empty($_COOKIE['chaveunica'])) {
            echo '<button class="btn-canvas fab-option-user">
            <i class="bi bi-person"></i> ' . $_COOKIE['chaveunica'] . '
        </button>';
        }
        ?>

        <button class="btn-canvas fab-option">Aula <?php echo $nrAula;  ?></button>
        <button class="btn-canvas fab-option" data-target="canvas1"><i class="bi bi-book"></i> Lições</button>
        <button class="fab-option open-modal-my-btn"><i class="bi bi-paperclip"></i> Anexos</button>

        <button class="fab-option"
            onclick="window.location.href='pagina_modulos.php?var=<?php echo $encMdls; ?>#modulos'"><i
                class="bi bi-box"></i> Módulos</button>

        <button class="fab-option-atividade" onclick="window.location.href='redesocial_turmas/atividades.php?atv=<?php echo $encatv;  ?>'">
            <i class="bi bi-camera"></i> Atividade
        </button>
        <?php if (!empty($codigoUser)) {
            if ($codigoUser == '1') {
        ?>
                <button class="fab-option"
                    onclick="window.location.href='atendimentoonline.php?var=<?php echo $_GET['var']; ?>#modulos'"><i
                        class="bi bi-box"></i> Atendimento</button>
                <button class="fab-option" onclick="toggleChat()">
                    <i class="bi bi-chat"></i> Chat
                </button>
                <button class="fab-option" onclick="window.location.href='redesocial_turmas/turmas.php'">
                    <i class="bi bi-people"></i> Turmas
                </button>

                <?php
                if ($atividade == "1") {
                    $libatv = ('<i class="bi bi-unlock"></i> Aberto');
                    $class = "btn-success";
                    $lib = "0";
                } else {
                    $libatv = ('<i class="bi bi-lock" ></i> Fechado');
                    $class = "btn-danger";
                    $lib = "1";
                }
                $encIdLibera = encrypt($decPublic . "&" . $lib, $action = 'e');
                ?>
                <input type="date" class="fab-option" name="dataInput" id="dataInput" value="<?php echo $data;  ?>">

                <button class="fab-option" id="addlicao" onclick="addlicaonasala(this)"><i class="bi bi-box"></i>
                    <span id="retorno">Add Aula</span>
                </button>
                <button class="btn <?php echo $class;  ?>" id="liblicao" data-id="<?php echo $encIdLibera; ?>"
                    onclick="btliblicao(this)"><i class="bi bi-box"></i>
                    <span id="retornolib"><?php echo $libatv;  ?></span>
                </button>


        <?php }
        } ?>


    </div>
</div>

<script>
    function toggleFab() {
        const fabOptions = document.querySelector('.fab-options');
        fabOptions.classList.toggle('active');
        // console.log("Clicado no menu plus")
    }
</script>