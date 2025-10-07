<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: text/html; charset=utf-8');


$idCurso = $_POST['idCurso'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$idCurso = isset($idCurso) ? $idCurso : 0;
$idCurso = encrypt($idCurso, $action = 'd');
if (!$idCurso || !is_numeric($idCurso)) {
    echo '<div class="alert alert-danger">ID inválido.</div>';
    exit;
} else {

    $queryUpdate = $con->prepare("UPDATE new_sistema_midias_fotos_PJA 
            SET 
            favorito = :favorito, 
            datamf =:datamf, 
            horamf= :horamf 
            WHERE 
            codpublicacao = :idcurso 
            AND tipo = :tipo 
            AND pasta = :pasta
            ");
    $queryUpdate->bindParam(":imagem", $nomeArquivo);
    $queryUpdate->bindParam(":datamf", $data);
    $queryUpdate->bindParam(":horamf", $hora);
    $queryUpdate->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
    $queryUpdate->bindParam(":tipo", $tipo, PDO::PARAM_INT);
    $queryUpdate->bindParam(":pasta", $pasta, PDO::PARAM_INT);
    $queryUpdate->execute();
    echo '<div class="alert alert-success">Favoritado.</div>';
    exit;
};

$idCurso = encrypt($idCurso, $action = 'd');
$pasta = $_POST['pasta'];
$tipo = $_POST['tipo'];