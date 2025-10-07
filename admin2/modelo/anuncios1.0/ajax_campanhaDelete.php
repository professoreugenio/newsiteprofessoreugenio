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

    // verifica se existe
    $st = $con->prepare("SELECT codigocampanhaanuncio FROM a_site_anuncios_campanhas WHERE codigocampanhaanuncio = :id LIMIT 1");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
    if ($st->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Campanha não encontrada']);
        exit;
    }

    $del = $con->prepare("DELETE FROM a_site_anuncios_campanhas WHERE codigocampanhaanuncio = :id LIMIT 1");
    $del->bindValue(':id', $id, PDO::PARAM_INT);
    $del->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Campanha excluída com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir: ' . $e->getMessage()]);
}
