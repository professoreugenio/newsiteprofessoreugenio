<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php'; ?>
<?php
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
} else if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}
$expUser = explode("&", $decUser);
$idTurma = $expUser['4'];
$query = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idturm ");
$query->bindParam(":idturm", $idTurma);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);
$idCurso = $rwNome['codcursost'];
?>
<div class="container-fluid mt-3">
    <button class="btn btn-outline-light d-lg-none mb-3" onclick="toggleSidebar()">☰ Menu</button>
    <div class="row">
        <div class="col-lg-9">
            <h2>Conteúdo da Aula <?php echo $codigoUsuario;  ?><?php echo $codigoTurma;  ?> </h2>
            <p>Aqui será exibido o conteúdo da aula selecionada.
                <?php echo $dec = encrypt($_COOKIE['nav'], $action = 'd'); ?>
            </p>
        </div>
        <div class="col-lg-3 sidebar">
            <div class="btn-container d-flex gap-2">
                <button id="btnAtuais" class="btn-custom btn-ativo" onclick="mostrarAulas('atuais')">Atuais</button>
                <button id="btnAnteriores" class="btn-custom btn-inativo"
                    onclick="mostrarAulas('anteriores')">Anteriores</button>
            </div>
            <div id="lista-aulas-atuais" class="list-group">
                <!-- <div class="list-group-item active">Windows</div> -->
                <?php
                $query = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos,new_sistema_publicacoes_PJA WHERE idcursopc = :idcurso AND codigopublicacoes=idpublicacaopc AND visivelpc='1' ");
                $query->bindParam(":idcurso", $idCurso);
                $query->execute();
                $fetch = $query->fetchALL();
                $quant = count($fetch);
                foreach ($fetch as $key => $value) { ?>

                <?php
                    $check="";
                
                $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario AND visivelpc='1'");
                $query->bindParam(":codigoaula", $codigoaula);
                $query->bindParam(":codigousuario", $codigousuario);
                $query->execute();
                $rwaulavista= $query->fetch(PDO::FETCH_ASSOC);
                if($rwaulavista):
                $check="sim-";
                
                endif
               
                 ?>
                <?php $enc = encrypt($value['idpublicacaopc'] . "&" . $value['idmodulopc'], $action = 'e'); ?>
                <a class="list-group-item" style="cursor: pointer;"
                    onclick="window.location.href='actionCurso.php?var=<?php echo $enc; ?>'; return false;">
                    <?php echo $check;  ?>
                    <?php echo $value['titulo'];  ?>
                </a>
                <?php }
                ?>
            </div>
            <div id="lista-aulas-anteriores" class="list-group d-none">
                <div class="list-group-item active">Windows</div>
                <a href="#" class="list-group-item">Histórico do Windows</a>
                <a href="#" class="list-group-item">Gerenciamento de Dispositivos</a>
                <a href="#" class="list-group-item">Segurança e Firewall</a>
                <a href="#" class="list-group-item">Modo de Recuperação</a>
                <a href="#" class="list-group-item">Windows Update</a>
                <div class="list-group-item active">Word</div>
                <a href="#" class="list-group-item">Numeração Automática</a>
                <a href="#" class="list-group-item">Referências e Citações</a>
                <a href="#" class="list-group-item">Trabalho com Índices</a>
                <a href="#" class="list-group-item">Criando Modelos</a>
                <a href="#" class="list-group-item">Modo de Revisão</a>
            </div>
        </div>
    </div>
</div>