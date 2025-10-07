<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método não permitido.');
    }

    $codigo = isset($_POST['codigo']) ? (int)$_POST['codigo'] : 0;
    $idpub  = isset($_POST['idpublicacao']) ? (int)$_POST['idpublicacao'] : 0;
    if ($codigo <= 0 || $idpub <= 0) {
        throw new InvalidArgumentException('Parâmetros inválidos.');
    }

    $pdo = config::connect();

    $st = $pdo->prepare("DELETE FROM a_curso_questionario 
                         WHERE codigoquestionario = :cod AND idpublicacaocq = :idpub");
    $st->bindValue(':cod', $codigo, PDO::PARAM_INT);
    $st->bindValue(':idpub', $idpub, PDO::PARAM_INT);
    $st->execute();

    if ($st->rowCount() < 1) {
        throw new RuntimeException('Pergunta não encontrada para esta publicação.');
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $th) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $th->getMessage()], JSON_UNESCAPED_UNICODE);
}
