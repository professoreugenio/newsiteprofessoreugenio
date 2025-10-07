<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.'];

try {
    if (!isset($_POST['idpublicacao'])) {
        throw new Exception("ID da publicação não recebido.");
    }

    $idPublicacao = encrypt(htmlspecialchars($_POST['idpublicacao']), 'd');

    $texto = trim($_POST['texto']);

    $con = config::connect();
    $queryUpdate = $con->prepare("UPDATE new_sistema_publicacoes_PJA SET
    texto = :texto,
    dataatualizacao = :dataatual,
    horaatualizacao = :horaatual
    WHERE codigopublicacoes =:idpub
");
    $queryUpdate->bindParam(":texto", $texto);
    $queryUpdate->bindParam(":dataatual", $data);
    $queryUpdate->bindParam(":horaatual", $hora);
    $queryUpdate->bindParam(":idpub", $idPublicacao);
    $queryUpdate->execute();

    if ($queryUpdate->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = "Publicação atualizada com sucesso!";
    } else {
        throw new Exception("Não foi possível atualizar a publicação.");
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
