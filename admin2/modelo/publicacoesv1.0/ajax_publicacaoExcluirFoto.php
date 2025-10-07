<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');
$response = ['sucesso' => false, 'mensagem' => 'Erro ao excluir a foto.'];

try {
    if (empty($_POST['idfoto'])) {
        throw new Exception("ID da foto não recebido.");
    }

    $idFoto = encrypt($_POST['idfoto'], 'd');

    $con = config::connect();
    $query = $con->prepare("SELECT foto, pasta FROM new_sistema_publicacoes_fotos_PJA WHERE codigomfotos = :id");
    $query->bindParam(":id", $idFoto);
    $query->execute();
    $dados = $query->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        throw new Exception("Foto não encontrada.");
    }

    $caminhoFoto = APP_ROOT . "/fotos/publicacoes/{$dados['pasta']}/{$dados['foto']}";
    if (file_exists($caminhoFoto)) {
        unlink($caminhoFoto);
    }

    $delete = $con->prepare("DELETE FROM new_sistema_publicacoes_fotos_PJA WHERE codigomfotos = :id");
    $delete->bindParam(":id", $idFoto);
    $delete->execute();

    $response['sucesso'] = true;
    $response['mensagem'] = "Foto excluída com sucesso.";
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
