<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$idGaleria = $_GET['id'] ?? 0;
$idGaleria= encrypt($idGaleria, $action = 'd' );
$query = $con->prepare("SELECT * FROM a_site_banco_imagens WHERE codigobancoimagens  = :id ");
$query->bindParam(":id", $idGaleria, PDO::PARAM_INT);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);
$nmGaleria = $rwNome['tituloBI'];
$pastaGaleria = $rwNome['pastaBI'];
