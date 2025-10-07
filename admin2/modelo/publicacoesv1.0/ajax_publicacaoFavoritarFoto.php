<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');
$response = ['sucesso' => false, 'mensagem' => 'Erro ao favoritar.'];

try {
    if (empty($_POST['idfoto'])) {
        throw new Exception("ID da foto não recebido.");
    }

    $idFoto = encrypt($_POST['idfoto'], 'd');

    $con = config::connect();

    // Remove favoritismo de outras fotos da mesma publicação
    $queryInfo = $con->prepare("SELECT codpublicacao FROM new_sistema_publicacoes_fotos_PJA WHERE codigomfotos = :id");
    $queryInfo->bindParam(":id", $idFoto);
    $queryInfo->execute();
    $dados = $queryInfo->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        throw new Exception("Foto não encontrada.");
    }

    $idPublicacao = $dados['codpublicacao'];

    // Zera todos os favoritos da publicação
    $reset = $con->prepare("UPDATE new_sistema_publicacoes_fotos_PJA SET favorito_pf = 0 WHERE codpublicacao = :idpub");
    $reset->bindParam(":idpub", $idPublicacao);
    $reset->execute();

    // Define a foto atual como favorita
    $fav = $con->prepare("UPDATE new_sistema_publicacoes_fotos_PJA SET favorito_pf = 1, data = :data, hora = :hora WHERE codigomfotos = :id");
    $fav->bindParam(":id", $idFoto);
    $fav->bindParam(":data", $data);
    $fav->bindParam(":hora", $hora);
    $fav->execute();

    $response['sucesso'] = true;
    $response['mensagem'] = "Foto definida como favorita.";
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
