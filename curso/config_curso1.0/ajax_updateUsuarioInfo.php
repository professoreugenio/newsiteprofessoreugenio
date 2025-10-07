<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $idCriptografado = $_POST['iduser'] ?? '';

    $permitidos = ['possuipc', 'datanascimento_sc'];
    if (!in_array($campo, $permitidos)) {
        echo json_encode(['sucesso' => false, 'msg' => 'Campo inválido.']);
        exit;
    }

    if (empty($idCriptografado)) {
        echo json_encode(['sucesso' => false, 'msg' => 'ID do usuário ausente.']);
        exit;
    }

    // Decodifica o ID
    $idUserDecoded = encrypt($idCriptografado, 'd')??null;
    // $exp = explode("&", $dec);
    // $idUserDecoded = $exp[0] ?? null;

    if (!$idUserDecoded) {
        echo json_encode(['sucesso' => false, 'msg' => 'ID inválido.'. $idUserDecoded]);
        exit;
    }

    // Atualiza o campo
    $query = $con->prepare("UPDATE new_sistema_cadastro SET $campo = :valor WHERE codigocadastro = :id");
    $query->bindParam(':valor', $valor);
    $query->bindParam(':id', $idUserDecoded);
    $query->execute();

    echo json_encode(['sucesso' => true]);
}
