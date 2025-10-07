<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');
$response = ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar o curso.'];

try {
    $nome = trim($_POST['nome']);
    $pasta = trim($_POST['pasta']);
    $youtube = trim($_POST['youtube']);
    $linkexterno = trim($_POST['linkexterno']);
    $bgcolor = trim($_POST['bgcolor']);

    $onlinesc = isset($_POST['onlinesc']) ? 1 : 0;
    $comercialsc = isset($_POST['comercialsc']) ? 1 : 0;
    $visivelsc = isset($_POST['visivelsc']) ? 1 : 0;
    $visivelhomesc = isset($_POST['visivelhomesc']) ? 1 : 0;

    if ($nome === '') {
        throw new Exception("O nome do curso é obrigatório.");
    }

    $sql = "INSERT INTO new_sistema_categorias_PJA (
                nome, pasta, youtubeurl, linkexterno, bgcolor,
                onlinesc, comercialsc, visivelsc, visivelhomesc, datasc, horasc
            ) VALUES (
                :nome, :pasta, :youtube, :linkexterno, :bgcolor,
                :onlinesc, :comercialsc, :visivelsc, :visivelhomesc, :datasc, :horasc
            )";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':pasta', $pasta);
    $stmt->bindParam(':youtube', $youtube);
    $stmt->bindParam(':linkexterno', $linkexterno);
    $stmt->bindParam(':bgcolor', $bgcolor);
    $stmt->bindParam(':onlinesc', $onlinesc, PDO::PARAM_INT);
    $stmt->bindParam(':comercialsc', $comercialsc, PDO::PARAM_INT);
    $stmt->bindParam(':visivelsc', $visivelsc, PDO::PARAM_INT);
    $stmt->bindParam(':visivelhomesc', $visivelhomesc, PDO::PARAM_INT);
    $stmt->bindParam(':datasc', $data);
    $stmt->bindParam(':horasc', $hora);

    if ($stmt->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = "Curso cadastrado com sucesso!";
    } else {
        throw new Exception("Erro ao inserir no banco.");
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
