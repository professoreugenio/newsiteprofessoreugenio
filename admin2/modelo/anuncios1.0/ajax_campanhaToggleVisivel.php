<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pega estado atual
    $st = $con->prepare("SELECT visivelACAM FROM a_site_anuncios_campanhas WHERE codigocampanhaanuncio = :id LIMIT 1");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
    if ($st->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Campanha não encontrada']);
        exit;
    }
    $atual = (int)$st->fetchColumn();

    // Alterna
    $novo = $atual === 1 ? 0 : 1;
    $up = $con->prepare("UPDATE a_site_anuncios_campanhas SET visivelACAM = :v WHERE codigocampanhaanuncio = :id LIMIT 1");
    $up->bindValue(':v', $novo, PDO::PARAM_INT);
    $up->bindValue(':id', $id, PDO::PARAM_INT);
    $up->execute();

    echo json_encode(['status' => 'ok', 'visivel' => $novo]);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao alternar: ' . $e->getMessage()]);
}
