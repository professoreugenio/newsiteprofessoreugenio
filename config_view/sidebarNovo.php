<?php
$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_msg_alunos WHERE idartigo_sma = :idartigo AND idturmasam = :idturma ");
$query->bindParam(":idturma", $idTurma);
$query->bindParam(":idartigo", $idoriginal);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);
if ($rwNome && isset($rwNome['codigomsg'])) {
    $idmsgpostMsg = $rwNome['codigomsg'];
    $encatv = encrypt($idmsgpostMsg . "&" . $idTurma, $action = 'e');
} else {
    // Defina um comportamento padrão ou uma mensagem de erro
    $encatv = ''; // ou null, ou redirecionar, ou lançar exceção, conforme sua lógica
}
?>
<div id="sidebar" class="sidebar">

    <?php
                $con = config::connect();
                $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_anexos_PJA WHERE codpublicacao = :var AND visivel = 1 ");
                $query->bindParam(":var", $idcopia);
                $query->execute();
                $fetch = $query->fetchALL();
                $quantAnexo = count($fetch);
                if($quantAnexo>0):
                ?>
    <button class="open-modal-my-btn" data-bs-toggle="tooltip" data-bs-placement="left" title="Anexo">
        <i class="bi bi-paperclip"></i>
    </button>
    <?php endif; ?>
    <button data-bs-toggle="tooltip" onclick="window.location.href='pagina_modulos.php?var=<?php echo $encMdls; ?>#modulos'" data-bs-placement="left" title="Módulos">
        <i class="bi bi-layers"></i>
    </button>
    <button onclick="window.location.href='redesocial_turmas/atividades.php?atv=<?php echo $encatv;  ?>'" data-bs-toggle="tooltip" data-bs-placement="left" title="Atividades">
        <i class="bi bi-camera"></i>
    </button>
    <button data-target="canvas1" title="Lições" data-bs-toggle="tooltip" data-bs-placement="left" title="Lições">
        <i class="bi bi-list-check"></i>
    </button>
    <button onclick="window.location.href='redesocial_turmas/turmas.php'" data-bs-toggle="tooltip" data-bs-placement="left" title="Turmas">
        <i class="bi bi-people-fill"></i>
    </button>
    <button onclick="window.location.href='/redesocial_turmas'" data-bs-toggle="tooltip" data-bs-placement="left" title="Sala de Aula">
        <i class="bi bi-easel-fill"></i>
    </button>
    <?php if (!empty($codigoUser)) {
        if ($codigoUser == '1') {
    ?>
            <button class="bg_warning" data-target="canvas1" title="Lições" data-bs-toggle="tooltip" data-bs-placement="left" title="Tópicos">
                <i class="bi bi-list-check"></i>*
            </button>
            <button onclick="toggleChat()" data-bs-toggle="tooltip" data-bs-placement="left" title="Atendimento">
                <i class="bi bi-chat"></i>
            </button>
            <?php
            if ($atividade == "1") {
                $libatv = ('<i class="bi bi-unlock"></i> ');
                $class = "bg-success";
                $lib = "0";
            } else {
                $libatv = ('<i class="bi bi-lock" ></i> ');
                $class = "bg-danger";
                $lib = "1";
            }
            $encIdLibera = encrypt($decPublic . "&" . $lib, $action = 'e');
            ?>
            <div class="date-wrapper">
                <i class="bi bi-calendar3 calendar-icon" onclick="abrirCalendario()"></i>
                <input type="date" name="dataInput" id="dataInput" value="<?php echo $data; ?>">
            </div>
            <script>
                function abrirCalendario() {
                    document.getElementById('dataInput').showPicker();
                }
            </script>
            <!-- <input type="date" name="dataInput" id="dataInput" value="<?php echo $data;  ?>"> -->
            <button id="addlicao" onclick="addlicaonasala(this)" data-bs-toggle="tooltip" data-bs-placement="left" title="Adicionar Lição">
                <span id="retorno">+</span>
            </button>
            <button class="<?php echo $class;  ?>" id="liblicao" data-id="<?php echo $encIdLibera; ?>"
                onclick="btliblicao(this)" data-bs-toggle="tooltip" data-bs-placement="left" title="Libera Atividade">
                <span id="retornolib"><?php echo $libatv;  ?></span>
            </button>
    <?php }
    } ?>
    <!-- Botão dentro da sidebar -->
    <button class="toggle-button-menu" id="hideSidebarBtn" data-bs-toggle="tooltip" data-bs-placement="left"
        title="Ocultar Menu">
        <i class="bi bi-list"></i>
    </button>
</div>